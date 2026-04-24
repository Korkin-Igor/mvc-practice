<?php

namespace Controller;

use Model\User;
use Service\ApiTokenService;
use Service\AuthService;
use Service\BookingService;
use Service\CatalogService;
use Src\Auth\Auth;
use Src\Request;
use Src\View;

class ApiController extends BaseController
{
    private AuthService $authService;
    private ApiTokenService $tokenService;
    private CatalogService $catalogService;
    private BookingService $bookingService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->tokenService = app()->tokenService;
        $this->catalogService = new CatalogService();
        $this->bookingService = new BookingService();
    }

    public function login(Request $request): void
    {
        $validator = $this->validate($request->all(), [
            'login' => ['required'],
            'password' => ['required'],
        ], [
            'required' => 'Поле :field пусто',
        ]);

        if ($validator->fails()) {
            $this->respond([
                'message' => $this->validationMessage($validator),
            ], 422);
        }

        $result = $this->authService->attemptLogin($request->all());
        if (!$result->isSuccess()) {
            $this->respond([
                'message' => $result->getMessage(),
            ], 401);
        }

        $user = Auth::user();
        if (!$user instanceof User) {
            $this->respond([
                'message' => 'Не удалось определить пользователя после авторизации.',
            ], 500);
        }

        $token = $this->tokenService->issueForUser($user);
        Auth::logout();

        if (!$token) {
            $this->respond([
                'message' => 'Не удалось выпустить токен доступа.',
            ], 500);
        }

        $this->respond([
            'message' => 'Авторизация выполнена.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    public function logout(Request $request): void
    {
        $token = $request->bearerToken();
        if ($token) {
            $this->tokenService->revoke($token);
        }

        Auth::logout();

        $this->respond([
            'message' => 'Токен отозван.',
        ]);
    }

    public function catalog(Request $request): void
    {
        $search = $this->input($request, 'q');
        $user = Auth::user();
        $openBookingBookIds = [];

        if ($user instanceof User && $user->isReader()) {
            $openBookingBookIds = $this->bookingService->getReaderOpenBookingBookIds($user);
        }

        $this->respond([
            'data' => $this->catalogService->getCatalogBooks($search, $openBookingBookIds),
        ]);
    }

    public function myBookings(Request $request): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->respond(['message' => 'Пользователь не найден.'], 401);
        }

        $this->respond([
            'data' => $this->bookingService->getReaderBookings($user),
        ]);
    }

    public function reserveBook(int $id, Request $request): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->respond(['message' => 'Пользователь не найден.'], 401);
        }

        $result = $this->bookingService->reserveBook($user, $id);

        $this->respond([
            'message' => $result->getMessage(),
        ], $result->isSuccess() ? 201 : 422);
    }

    public function extendBooking(int $id, Request $request): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->respond(['message' => 'Пользователь не найден.'], 401);
        }

        $result = $this->bookingService->extendBooking($user, $id);

        $this->respond([
            'message' => $result->getMessage(),
        ], $result->isSuccess() ? 200 : 422);
    }

    public function librarianBookings(Request $request): void
    {
        $this->respond([
            'data' => $this->bookingService->getLibrarianBookings(),
        ]);
    }

    public function approveBooking(int $id, Request $request): void
    {
        $this->updateBooking($id, 'approve');
    }

    public function rejectBooking(int $id, Request $request): void
    {
        $this->updateBooking($id, 'reject');
    }

    public function returnBooking(int $id, Request $request): void
    {
        $this->updateBooking($id, 'return');
    }

    private function updateBooking(int $id, string $action): void
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->respond(['message' => 'Пользователь не найден.'], 401);
        }

        $result = $this->bookingService->updateByLibrarian($user, $id, $action);

        $this->respond([
            'message' => $result->getMessage(),
        ], $result->isSuccess() ? 200 : 422);
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();
        return $user instanceof User ? $user : null;
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => $user->name,
            'login' => $user->login,
            'role' => $user->isLibrarian() ? 'librarian' : 'reader',
        ];
    }

    private function respond(array $payload, int $code = 200): void
    {
        (new View())->toJSON($payload, $code);
    }
}
