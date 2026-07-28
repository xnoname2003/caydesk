<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Priority;

function createUserWithRole(string $roleName): User {
    $user = User::factory()->create();
    $user->assignRole($roleName);
    return $user;
}

test('unauthenticated users cannot access the ticket API', function () {
    $response = $this->getJson('/api/tickets');
    $response->assertStatus(401);
});

test('authenticated user (e.g., customer) can create a new ticket via API', function () {
    $customer = createUserWithRole('customer');
    $category = Category::create(['name' => 'Technical Support']);
    $priority = Priority::create(['name' => 'High', 'color' => 'danger']);

    $payload = [
        'title' => 'Server Issue',
        'description' => 'The server is down!',
        'category_id' => $category->id,
        'priority_id' => $priority->id,
    ];

    $response = $this->actingAs($customer)->postJson('/api/tickets/create', $payload);

    $response->assertStatus(201)
             ->assertJsonPath('message', 'Ticket successfully created!');

    $this->assertDatabaseHas('tickets', [
        'title' => 'Server Issue',
        'created_by' => $customer->id,
    ]);
});

test('administrator can create a ticket and set assigned_agent_id', function () {
    $admin = createUserWithRole('administrator');
    $agent = createUserWithRole('agent');
    $category = Category::create(['name' => 'Internal Task']);
    $priority = Priority::create(['name' => 'Medium', 'color' => 'warning']);

    $payload = [
        'title' => 'Maintenance Task',
        'description' => 'Check server cables',
        'category_id' => $category->id,
        'priority_id' => $priority->id,
        'assigned_agent_id' => $agent->id, 
    ];

    $response = $this->actingAs($admin)->postJson('/api/tickets/create', $payload);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('tickets', [
        'title' => 'Maintenance Task',
        'created_by' => $admin->id,
        'assigned_agent_id' => $agent->id,
    ]);
});

test('assigned_agent_id input is ignored if sent by customer', function () {
    $customer = createUserWithRole('customer');
    $agent = createUserWithRole('agent');
    $category = Category::create(['name' => 'Technical Support']);
    $priority = Priority::create(['name' => 'High', 'color' => 'danger']);

    $payload = [
        'title' => 'Unauthorized Assignment Attempt',
        'description' => 'Trying to assign an agent manually',
        'category_id' => $category->id,
        'priority_id' => $priority->id,
        'assigned_agent_id' => $agent->id,
    ];

    $response = $this->actingAs($customer)->postJson('/api/tickets/create', $payload);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('tickets', [
        'title' => 'Unauthorized Assignment Attempt',
        'created_by' => $customer->id,
        'assigned_agent_id' => null, 
    ]);
});

test('internal notes are strictly hidden from customers in API response', function () {
    $customer = createUserWithRole('customer');
    $agent = createUserWithRole('agent');
    $category = Category::create(['name' => 'Billing']);
    $priority = Priority::create(['name' => 'Low', 'color' => 'gray']);

    $createResponse = $this->actingAs($customer)->postJson('/api/tickets/create', [
        'title' => 'Billing Error',
        'description' => 'Why is my bill duplicated?',
        'category_id' => $category->id,
        'priority_id' => $priority->id,
    ]);
    
    $ticketNumber = $createResponse->json('data.ticket_number');

    $this->actingAs($customer)->postJson("/api/tickets/{$ticketNumber}/comment/reply", [
        'content' => 'Please help me immediately.',
        'is_internal' => false,
    ]);

    $this->actingAs($agent)->postJson("/api/tickets/{$ticketNumber}/comment/reply", [
        'content' => 'SECRET: Our billing system has a bug, do not tell the user.',
        'is_internal' => true,
    ]);

    $response = $this->actingAs($customer)->getJson("/api/tickets/{$ticketNumber}");
    $response->assertStatus(200);
    $response->assertDontSee('SECRET: Our billing system has a bug, do not tell the user.');
    $response->assertSee('Please help me immediately.');
});
