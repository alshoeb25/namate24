<?php

namespace App\Filament\Resources\ReferralResource\Pages;

use App\Filament\Resources\ReferralResource;
use App\Models\Referral;
use App\Models\User;
use App\Models\CoinTransaction;
use App\Models\AdminActionLog;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ManualReferral extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = ReferralResource::class;
    protected static string $view = 'filament.resources.referral-resource.pages.manual-referral';
    protected static ?string $title = 'Manual Referral Entry';
    protected static ?string $navigationLabel = 'Manual Referral';

    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Manual Referral Setup')
                    ->description('Create a manual referral relationship and award coins when automatic system fails.')
                    ->schema([
                        Forms\Components\Select::make('referrer_id')
                            ->label('Referrer (Person who referred)')
                            ->placeholder('Select the user who referred')
                            ->options(function () {
                                return User::query()
                                    ->whereNotNull('referral_code')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->helperText('The user who shared their referral code'),

                        Forms\Components\Select::make('referred_id')
                            ->label('Referred User (Person who was referred)')
                            ->placeholder('Select the user who got referred')
                            ->options(function (callable $get) {
                                $referrerId = $get('referrer_id');
                                return User::query()
                                    ->when($referrerId, fn($q) => $q->where('id', '!=', $referrerId))
                                    ->whereNull('referred_by') // Only show users not already referred
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->helperText('Only users who haven\'t been referred yet'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('referrer_coins_display')
                                    ->label('Coins for Referrer')
                                    ->content(fn () => config('coins.referral_bonus', 30) . ' coins')
                                    ->helperText('Coins to award to the person who referred'),

                                Forms\Components\Placeholder::make('referred_coins_display')
                                    ->label('Coins for Referred User')
                                    ->content(fn () => config('coins.referral_reward', 15) . ' coins')
                                    ->helperText('Coins to award to the person who got referred'),
                            ]),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3)
                            ->helperText('Optional notes explaining why this manual entry was needed'),
                    ]),
            ])
            ->statePath('data');
    }

    public function createManualReferral(): void
    {
        $data = $this->form->getState();

        // Validate
        if ($data['referrer_id'] === $data['referred_id']) {
            Notification::make()
                ->title('Error')
                ->body('Referrer and referred user cannot be the same person.')
                ->danger()
                ->send();
            return;
        }

        // Check if referred user already has a referrer
        $referred = User::find($data['referred_id']);
        if ($referred && $referred->referred_by) {
            Notification::make()
                ->title('User Already Referred')
                ->body("This user was already referred by another user. Cannot create duplicate referral.")
                ->warning()
                ->send();
            return;
        }

        // Check if relationship already exists
        $existingReferral = Referral::where('referrer_id', $data['referrer_id'])
            ->where('referred_id', $data['referred_id'])
            ->first();

        if ($existingReferral) {
            Notification::make()
                ->title('Already Exists')
                ->body('This referral relationship already exists in the system.')
                ->warning()
                ->send();
            return;
        }

        DB::beginTransaction();

        try {
            $referrer = User::findOrFail($data['referrer_id']);
            $referred = User::findOrFail($data['referred_id']);

            // Get coins from config
            $referrerCoins = config('coins.referral_bonus', 30);
            $referredCoins = config('coins.referral_reward', 15);

            \Log::info('Creating manual referral', [
                'referrer_id' => $referrer->id,
                'referred_id' => $referred->id,
                'referrer_coins' => $referrerCoins,
                'referred_coins' => $referredCoins,
            ]);

            // Update referred_by field FIRST (before creating referral)
            $referred->update([
                'referred_by' => $data['referrer_id'],
            ]);

            // Create referral record
            $referral = Referral::create([
                'referrer_id' => $data['referrer_id'],
                'referred_id' => $data['referred_id'],
                'referrer_coins' => $referrerCoins,
                'referred_coins' => $referredCoins,
                'reward_given' => true,
                'reward_given_at' => now(),
            ]);

            // Award coins to referrer
            $referrer->increment('coins', $referrerCoins);
            $referrer->refresh();
            
            \Log::info('Referrer coins updated', ['user_id' => $referrer->id, 'new_balance' => $referrer->coins]);
            
            // Update wallet if exists
            if ($referrer->wallet) {
                $referrer->wallet->increment('balance', $referrerCoins);
                \Log::info('Referrer wallet updated', ['wallet_id' => $referrer->wallet->id]);
            } else {
                \Log::warning('Referrer has no wallet', ['user_id' => $referrer->id]);
            }
            
            $transaction1 = CoinTransaction::create([
                'user_id' => $referrer->id,
                'amount' => $referrerCoins,
                'type' => 'referral_reward',
                'description' => "Referral reward for referring {$referred->name} (Manual Entry by Admin)",
                'balance_after' => $referrer->coins,
                'meta' => ['source' => 'manual_admin_referral'],
            ]);
            
            \Log::info('Referrer transaction created', ['transaction_id' => $transaction1->id]);

            // Award coins to referred user
            $referred->increment('coins', $referredCoins);
            $referred->refresh();
            
            \Log::info('Referred user coins updated', ['user_id' => $referred->id, 'new_balance' => $referred->coins]);
            
            // Update wallet if exists
            if ($referred->wallet) {
                $referred->wallet->increment('balance', $referredCoins);
                \Log::info('Referred user wallet updated', ['wallet_id' => $referred->wallet->id]);
            } else {
                \Log::warning('Referred user has no wallet', ['user_id' => $referred->id]);
            }
            
            $transaction2 = CoinTransaction::create([
                'user_id' => $referred->id,
                'amount' => $referredCoins,
                'type' => 'referral_bonus',
                'description' => "Referral bonus for being referred by {$referrer->name} (Manual Entry by Admin)",
                'balance_after' => $referred->coins,
                'meta' => ['source' => 'manual_admin_referral'],
            ]);
            
            \Log::info('Referred user transaction created', ['transaction_id' => $transaction2->id]);

            // Log admin action
            AdminActionLog::create([
                'admin_id' => auth()->id(),
                'action_type' => 'manual_referral_created',
                'subject_type' => Referral::class,
                'subject_id' => $referral->id,
                'metadata' => [
                    'referrer_id' => $referrer->id,
                    'referrer_name' => $referrer->name,
                    'referred_id' => $referred->id,
                    'referred_name' => $referred->name,
                    'referrer_coins' => $referrerCoins,
                    'referred_coins' => $referredCoins,
                    'admin_notes' => $data['admin_notes'] ?? null,
                ],
            ]);

            DB::commit();

            Notification::make()
                ->title('Success!')
                ->body("Referral created! {$referrer->name} received {$referrerCoins} coins and {$referred->name} received {$referredCoins} coins.")
                ->success()
                ->send();

            // Reset form
            $this->form->fill();
            
            // Redirect to list
            $this->redirect(ReferralResource::getUrl('index'));

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Manual referral creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? [],
            ]);
            
            Notification::make()
                ->title('Error')
                ->body('Failed to create manual referral: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
