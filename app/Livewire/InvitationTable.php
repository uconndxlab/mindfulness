<?php

namespace App\Livewire;

use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class InvitationTable extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';
    
    #[Url(except: 'all')]
    public $statusFilter = 'all';
    
    public $sortColumn = 'last_sent_at';
    public $sortDirection = 'desc';

    public $columns = [
        'email' => ['label' => 'Email', 'sortable' => true],
        'invited_by' => ['label' => 'Invited By', 'sortable' => false],
        'status' => ['label' => 'Status', 'sortable' => true],
        'last_sent_at' => ['label' => 'Sent', 'sortable' => true],
        'expires_at' => ['label' => 'Expires', 'sortable' => true],
        'used_at' => ['label' => 'Used', 'sortable' => true],
        'actions' => ['label' => 'Actions', 'sortable' => false],
    ];

    public function sortBy($column)
    {
        if (isset($this->columns[$column]['sortable']) && $this->columns[$column]['sortable']) {
            if ($this->sortColumn === $column) {
                $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                $this->sortColumn = $column;
                $this->sortDirection = 'asc';
            }
        }

        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        // auto-expire old pending invitations
        Invitation::where('status', 'pending')
            ->where('expires_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        // build query
        $invitationsQuery = Invitation::with(['invitedBy', 'registeredUser'])
            ->orderBy($this->sortColumn, $this->sortDirection);

        // filter
        if ($this->statusFilter !== 'all') {
            $invitationsQuery->where('status', $this->statusFilter);
        }

        // search
        if (!empty($this->search)) {
            $invitationsQuery->where(function ($query) {
                $query->where('email', 'like', '%' . $this->search . '%')
                    ->orWhereHas('invitedBy', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('status', 'like', '%' . $this->search . '%');
            });
        }

        $invitations = $invitationsQuery->paginate(10);

        return view('livewire.invitation-table', [
            'invitations' => $invitations
        ]);
    }

    public function resendInvitation($invitationId)
    {
        try {
            $invitation = Invitation::findOrFail($invitationId);

            // only resend pending invitations
            if ($invitation->status !== 'pending' && $invitation->status !== 'expired') {
                session()->flash('error', 'Can only resend pending invitations.');
                return;
            }

            // check if expired - mark as pending
            if ($invitation->status === 'expired' || $invitation->expires_at->isPast()) {
                // check for active pending invitations for this email
                $activePendingInvitations = Invitation::where('email', $invitation->email)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', Carbon::now())
                    ->count();
                if ($activePendingInvitations > 0) {
                    session()->flash('error', 'An active invitation already exists for this email.');
                    return;
                }
                $invitation->markAsPending();
            }
            else {
                $invitation->update([
                    'last_sent_at' => Carbon::now(),
                    'resend_count' => $invitation->resend_count + 1,
                ]);
            }

            // resend the email
            Mail::to($invitation->email)->send(new InvitationEmail($invitation));

            session()->flash('message', 'Invitation resent to ' . $invitation->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to resend invitation.');
        }
    }

    public function revokeInvitation($invitationId)
    {
        try {
            $invitation = Invitation::findOrFail($invitationId);

            // only revoke pending invitations
            if ($invitation->status !== 'pending') {
                session()->flash('error', 'Can only revoke pending invitations.');
                return;
            }

            $invitation->revoke();

            session()->flash('message', 'Invitation revoked for ' . $invitation->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to revoke invitation.');
        }
    }
}
