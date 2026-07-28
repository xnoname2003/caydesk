<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Ticket;
use App\Services\TicketStatusService;
use App\Services\TicketService;

function createTestUser(string $roleName): User
{
    $user = User::factory()->create();
    $user->assignRole($roleName);
    return $user;
}

function createDummyTicket(User $creator, ?User $agent = null, string $status = 'Open'): Ticket {
    $category = Category::firstOrCreate(['name' => 'General', 'description' => 'Test']);
    $priority = Priority::firstOrCreate(['name' => 'Low', 'color' => 'gray']);
    
    $ticketService = app(TicketService::class);
    
    $payload = [
        'title' => 'Test Ticket',
        'description' => 'This is a dummy ticket for testing.',
        'category_id' => $category->id,
        'priority_id' => $priority->id,
    ];

    if ($agent) {
        $payload['assigned_agent_id'] = $agent->id;
    }

    $ticket = $ticketService->createTicket($payload, $creator->id);

    if ($status !== 'Open') {
        $ticket->update(['status' => $status]);
    }

    return $ticket;
}

test('customer cannot assign an agent to a ticket', function () {
    $customer = createTestUser('customer');
    $agent = createTestUser('agent');
    $ticket = createDummyTicket($customer);

    $payload = ['assigned_agent_id' => $agent->id];

    $response = $this->actingAs($customer)->putJson("/api/tickets/{$ticket->ticket_number}/agent/assign", $payload);

    $response->assertStatus(403);
});

test('administrator can assign an agent to a ticket and status changes to Assigned', function () {
    $admin = createTestUser('administrator');
    $customer = createTestUser('customer');
    $agent = createTestUser('agent');
    $ticket = createDummyTicket($customer, null, TicketStatusService::STATUS_OPEN);

    $payload = ['assigned_agent_id' => $agent->id];

    $response = $this->actingAs($admin)->putJson("/api/tickets/{$ticket->ticket_number}/agent/assign", $payload);

    $response->assertStatus(200)
             ->assertJsonPath('message', 'Agent assigned successfully!');

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'assigned_agent_id' => $agent->id,
        'status' => TicketStatusService::STATUS_ASSIGNED,
    ]);
});

test('customer can close their own ticket', function () {
    $customer = createTestUser('customer');
    $ticket = createDummyTicket($customer, null, TicketStatusService::STATUS_RESOLVED);

    $response = $this->actingAs($customer)->putJson("/api/tickets/{$ticket->ticket_number}/status/close");

    $response->assertStatus(200)
             ->assertJsonPath('message', 'Ticket closed successfully!');

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => TicketStatusService::STATUS_CLOSED,
    ]);
});

test('customer cannot close someone else ticket', function () {
    $customerA = createTestUser('customer');
    $customerB = createTestUser('customer');
    
    $ticket = createDummyTicket($customerA, null, TicketStatusService::STATUS_RESOLVED);

    $response = $this->actingAs($customerB)->putJson("/api/tickets/{$ticket->ticket_number}/status/close");

    $response->assertStatus(403)
             ->assertJsonPath('message', 'You are not authorized to close this ticket.');
});

test('system rejects invalid ticket status transitions', function () {
    $admin = createTestUser('administrator');
    $customer = createTestUser('customer');
    
    $ticket = createDummyTicket($customer, null, TicketStatusService::STATUS_OPEN);

    $payload = ['status' => TicketStatusService::STATUS_RESOLVED];

    $response = $this->actingAs($admin)->putJson("/api/tickets/{$ticket->ticket_number}/status/edit", $payload);

    $response->assertStatus(422)
             ->assertJsonPath('message', 'Invalid status transition based on current ticket status.');
});