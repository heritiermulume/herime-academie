<?php

namespace Tests\Feature\Admin;

use App\Mail\CustomAnnouncementMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AnnouncementSendEmailDuplicateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customerA;

    private User $customerB;

    protected function setUp(): void
    {
        parent::setUp();

        // CommunicationService traite array/log comme non-succès ; on simule SMTP + Mail::fake().
        config(['mail.default' => 'smtp']);
        Mail::fake();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->customerA = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->customerB = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_after_selected_send_all_recipients_still_receive_all_broadcast_with_same_subject(): void
    {
        $subject = 'Même objet campagne';

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.send-email.post'), [
                'recipient_type' => 'selected',
                'user_ids' => $this->customerA->id.','.$this->customerB->id,
                'subject' => $subject,
                'email_content' => '<p>Test sélection</p>',
                'send_type' => 'now',
            ])
            ->assertRedirect(route('admin.announcements'));

        Mail::assertSent(CustomAnnouncementMail::class, 2);

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.send-email.post'), [
                'recipient_type' => 'all',
                'subject' => $subject,
                'email_content' => '<p>Test tous</p>',
                'send_type' => 'now',
            ])
            ->assertRedirect(route('admin.announcements'));

        // Admin + A + B = 3 pour « tous », en plus des 2 déjà envoyés en sélection.
        Mail::assertSent(CustomAnnouncementMail::class, 5);
    }

    public function test_second_all_broadcast_same_subject_within_five_minutes_sends_no_extra_mails(): void
    {
        $subject = 'Objet broadcast doublon';

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.send-email.post'), [
                'recipient_type' => 'all',
                'subject' => $subject,
                'email_content' => '<p>Premier envoi</p>',
                'send_type' => 'now',
            ])
            ->assertRedirect(route('admin.announcements'));

        Mail::assertSent(CustomAnnouncementMail::class, 3);

        Mail::fake();

        $this->actingAs($this->admin)
            ->post(route('admin.announcements.send-email.post'), [
                'recipient_type' => 'all',
                'subject' => $subject,
                'email_content' => '<p>Deuxième envoi</p>',
                'send_type' => 'now',
            ])
            ->assertRedirect(route('admin.announcements'));

        Mail::assertNothingSent();
    }
}
