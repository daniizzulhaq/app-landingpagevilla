<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\PropertymeTestCase;
class AdminAuthenticationTest extends PropertymeTestCase
{
    public function test_admin_routes_require_authentication_and_registration_does_not_exist(): void
    {
        foreach(['/admin','/admin/properties','/admin/settings/company','/admin/portfolio'] as $path) $this->get($path)->assertRedirect('/admin/login');
        $this->post('/admin/properties',[])->assertRedirect('/admin/login'); $this->get('/register')->assertNotFound(); $this->post('/register',[])->assertStatus(405);
    }
    public function test_login_logout_and_hashed_passwords(): void
    {
        $password='Testing!Secure123'; $user=User::factory()->create(['password'=>Hash::make($password)]);
        $this->post('/admin/login',['email'=>$user->email,'password'=>$password])->assertRedirect('/admin'); $this->assertAuthenticatedAs($user); $this->assertNotSame($password,$user->password);
        $this->get('/admin')->assertOk()->assertSee('Recent Activity')->assertHeader('X-Robots-Tag','noindex, nofollow');
        $this->post('/admin/logout')->assertRedirect('/admin/login'); $this->assertGuest();
    }
    public function test_login_is_rate_limited(): void
    {
        for($i=0;$i<6;$i++) $response=$this->post('/admin/login',['email'=>'unknown@example.test','password'=>'incorrect']);
        $response->assertSessionHasErrors('email'); $this->assertStringContainsString('Too many attempts',session('errors')->first('email'));
    }
    public function test_password_reset_is_disabled_until_configured(): void { $this->get('/admin/forgot-password')->assertNotFound(); }
    public function test_profile_requires_current_password(): void
    {
        $user=User::factory()->create(); $this->actingAs($user)->put('/admin/profile',['name'=>'Changed','email'=>$user->email,'current_password'=>'wrong'])->assertSessionHasErrors('current_password');
        $this->assertSame($user->name,$user->fresh()->name);
    }
    public function test_csrf_protection_rejects_a_login_without_a_token(): void
    {
        $this->app['env']='local';
        $this->post('/admin/login',['email'=>'test@example.test','password'=>'anything'])->assertStatus(419);
    }
    public function test_configured_password_reset_uses_one_time_tokens(): void
    {
        config(['propertyme.password_reset_enabled'=>true]); $user=User::factory()->create();
        $token=\Illuminate\Support\Facades\Password::broker()->createToken($user);
        $this->get('/admin/reset-password/'.$token.'?email='.urlencode($user->email))->assertOk();
        $data=['token'=>$token,'email'=>$user->email,'password'=>'New!SecurePassword123','password_confirmation'=>'New!SecurePassword123'];
        $this->post('/admin/reset-password',$data)->assertRedirect('/admin/login'); $this->assertTrue(Hash::check($data['password'],$user->fresh()->password));
        $this->post('/admin/reset-password',$data)->assertSessionHasErrors('email');
    }
    public function test_initial_admin_command_provisions_an_account_without_a_public_registration(): void
    {
        $this->artisan('propertyme:admin',['email'=>'setup@example.test','--name'=>'Setup Admin'])->expectsQuestion('Password (12+ characters, upper/lowercase, number, symbol)','Setup!Secure123')->expectsQuestion('Confirm password','Setup!Secure123')->expectsOutput('Admin account created.')->assertSuccessful();
        $user=User::where('email','setup@example.test')->firstOrFail(); $this->assertTrue(Hash::check('Setup!Secure123',$user->password));
    }
}
