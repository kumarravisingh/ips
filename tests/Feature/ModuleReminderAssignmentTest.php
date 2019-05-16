<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModuleReminderAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function testAssignmentOfModuleReminder()
    {
        $response = $this->post('api/module_reminder_assigner',
            [
                'contact_email'=>'test@user.com'
            ]
        );
        //echo $response->getContent();
        $response->assertStatus(200);
        $response->assertExactJson(['success'=>true]);


    }
}
