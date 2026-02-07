<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage\ImageFileUploadProcessor;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\PassportUserAuthentication;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases\UpdateUserPassword;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Requests\ProfileRequest;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Requests\UserNewPasswordVerifierRequest;
use BilliftyResumeSDK\SharedResources\Modules\User\Http\Requests\UserRequest;
use BilliftyResumeSDK\SharedResources\Modules\User\Jobs\SendPasswordChangedEmailJob;
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
    public function update(
		ProfileRequest $request,
		int $id,
		UserRepository $user
	) {
		$data = $request->validated();
		$userModel = $user->find($id);

		if (!empty($data['avatar'])) {
			$imageProcessor = ImageFileUploadProcessor::make($data['avatar'], $data['avatar']->getClientOriginalName(), 'profile-image');
			$storedPath = $imageProcessor->store();
			unset($data['avatar']);
			$imageProcessor->deleteLastFile('avatar', $userModel->info);
		} else {
			$storedPath = $userModel->info['avatar'] ?? null;
		}

		return $user->save(array_merge($data, ['info' => [...$data['info'], 'avatar' => $storedPath]]), $userModel);
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

	public function me()
	{
		$user = auth()->user();

		abort_unless($user, 401);

		$user->load('info');

		return response()->json($user);
	}

	public function updatePassword(UserNewPasswordVerifierRequest $request, UpdateUserPassword $passwordUpdater)
	{
		$data = $request->validated();
		$user = auth()->user();

		$passwordUpdater->execute(auth()->user()->id, $data['currentPassword'], $data['newPassword']);

		SendPasswordChangedEmailJob::dispatch(
			$user->email,
			$user->name
		);
	}

}
