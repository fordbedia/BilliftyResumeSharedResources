<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\PassportUserAuthentication;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Requests\UserRequest;
use DomainException;
use Illuminate\Http\Request;
use InvalidArgumentException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
		UserRequest $request,
		UserRepository $user,
		PassportUserAuthentication $auth
	) {
        $data = $request->validated();

		$user->create($data);

		// Proceed to login
		try {
            return response()->json($auth->handle($data['email'], $data['password']));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

	public function authenticate(Request $request, PassportUserAuthentication $auth)
	{
		$data = $request->validate([
			'email' => 'required|email',
			'password' => 'required|string'
		]);

		try {
            return response()->json($auth->handle($data['email'], $data['password']));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
	}
}
