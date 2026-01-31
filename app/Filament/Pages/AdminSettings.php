<?php

namespace App\Filament\Pages;

use App\Models\AdminSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;

class AdminSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.admin-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'enquiry_post_fee' => AdminSetting::get('enquiry_post_fee', 10),
            'enquiry_unlock_fee' => AdminSetting::get('enquiry_unlock_fee', 5),
            'contact_unlock_coins' => AdminSetting::get('contact_unlock_coins', 50),
            'referral_reward_coins' => AdminSetting::get('referral_reward_coins', 50),
            'referral_bonus_coins' => AdminSetting::get('referral_bonus_coins', 25),
            'max_leads_per_enquiry' => AdminSetting::get('max_leads_per_enquiry', 5),
            'support_email' => AdminSetting::get('support_email', 'support@namate24.com'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Coin System')
                    ->description('Configure coin values and fees')
                    ->schema([
                        TextInput::make('enquiry_post_fee')
                            ->label('Enquiry Post Fee (coins)')
                            ->numeric(),
                        TextInput::make('enquiry_unlock_fee')
                            ->label('Enquiry Unlock Fee (coins)')
                            ->numeric(),
                        TextInput::make('contact_unlock_coins')
                            ->label('Contact Unlock Fee (coins)')
                            ->numeric(),
                        TextInput::make('referral_reward_coins')
                            ->label('Referral Reward Coins')
                            ->numeric(),
                        TextInput::make('referral_bonus_coins')
                            ->label('Referral Bonus Coins (New User)')
                            ->numeric(),
                    ])
                    ->columns(2),

                Section::make('Enquiry System')
                    ->description('Configure enquiry limits')
                    ->schema([
                        TextInput::make('max_leads_per_enquiry')
                            ->label('Max Leads per Enquiry')
                            ->numeric(),
                    ]),

                Section::make('Email Settings')
                    ->description('Email configuration')
                    ->schema([
                        TextInput::make('support_email')
                            ->label('Support Email')
                            ->email(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            AdminSetting::set($key, $value, is_numeric($value) ? 'integer' : 'string');
        }

        $this->notify('success', 'Settings updated successfully.');
    }
}
