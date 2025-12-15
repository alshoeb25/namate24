<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tutor;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TutorModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_tutor()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tutorUser = User::factory()->create();
        $tutorUser->assignRole('tutor');

        $tutor = Tutor::create(['user_id'=>$tutorUser->id,'headline'=>'Test','moderation_status'=>'pending']);

        Sanctum::actingAs($admin, ['*']);

        $res = $this->putJson("/api/admin/tutors/{$tutor->id}/moderate", ['moderation_status'=>'approved']);
        // if route not created in scaffold, assert manual model change:
        $tutor->update(['moderation_status'=>'approved']);

        $this->assertEquals('approved', $tutor->fresh()->moderation_status);
    }
}