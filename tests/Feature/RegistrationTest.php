<?php

namespace Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;
use App\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_register_form()
    {
        $response = $this->get('/register');
        $response->assertSuccessful();
        $response->assertViewIs('auth.register');
    }
    public function test_can_register_with_valid_data()
{
   $data = [
      'name' => 'John Doe',
      'email' => 'johndoe@example.com',
      'password' => 'password',
      'password_confirmation' => 'password'
   ];
   
   $response = $this->post('/register', $data);
   
   $response->assertStatus(302);
   $this->assertAuthenticated();
   $response->assertRedirect('/dashboard');
}
public function test_cannot_register_with_existing_email()
{
   $existingUser = User::factory()->create();
   
   $data = [
      'name' => 'John Doe',
      'email' => $existingUser->email,
      'password' => 'password',
      'password_confirmation' => 'password'
   ];
   
   $response = $this->post('/register', $data);
   
   $response->assertSessionHasErrors('email');
   $this->assertGuest();
}
public function test_cannot_register_with_invalid_email()
{
   $data = [
      'name' => 'John Doe',
      'email' => 'johndoeexample.com',
      'password' => 'password',
      'password_confirmation' => 'password'
   ];
   
   $response = $this->post('/register', $data);
   
   $response->assertSessionHasErrors('email');
   $this->assertGuest();
}
public function test_cannot_register_with_password_less_than_eight_characters()
{
   $data = [
      'name' => 'John Doe',
      'email' => 'johndoe@example.com',
      'password' => 'passwor',
      'password_confirmation' => 'passwor'
   ];
   
   $response = $this->post('/register', $data);
   
   $response->assertSessionHasErrors('password');
   $this->assertGuest();
}
public function test_cannot_register_with_password_confirmation_not_matching()
{
   $data = [
      'name' => 'John Doe',
      'email' => 'johndoe@example.com',
      'password' => 'password',
      'password_confirmation' => 'password1'
   ];
   
   $response = $this->post('/register', $data);
   
   $response->assertSessionHasErrors('password');
   $this->assertGuest();
}
public function test_cannot_register_with_missing_name()
{
   $data = [
      'name' => '',
      'email' => 'johndoe@example.com',
      'password' => 'password',
      'password_confirmation' => 'password'
   ];
   
   $response = $this->post('/register', $data);
   
   $response->assertSessionHasErrors('name');
   $this->assertGuest();
}
public function test_cannot_register_with_missing_email()
{
   $data = [
      'name' => 'John Doe',
      'email' => '',
      'password' => 'password',
      'password_confirmation' => 'password'
   ];
   
   $response = $this->post('/register', $data);
   
   $response->assertSessionHasErrors('email');
   $this->assertGuest();
}
public function test_cannot_register_with_missing_password()
{
   $data = [
      'name' => 'John Doe',
      'email' => 'johndoe@example.com',
      'password' => '',
      'password_confirmation' => ''
   ];
   
   $response = $this->post('/register', $data);
   
   $response->assertSessionHasErrors('password');
   $this->assertGuest();
}
public function test_user_cannot_login_before_email_verification()
{
   $user = User::factory()->create([
      'email_verified_at' => null,
   ]);
   
   $response = $this->actingAs($user)->get('/dashboard');
   
   $response->assertRedirect('/email/verify');
}
public function test_user_can_login_after_email_verification()
{
   $user = User::factory()->create([
      'email_verified_at' => now(),
   ]);
   
   $response = $this->actingAs($user)->get('/dashboard');
   
   $response->assertSuccessful();
   $response->assertSee('Dashboard');
}
}