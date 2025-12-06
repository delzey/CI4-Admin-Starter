<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MessageRecipientsModel;

/**
 * 
 * 
 * With the service:
 * 
 * service('messageService')->send(
 *     fromUser: auth()->id(),
 *     toUsers:  $targetUserId,
 *     subject:  'Hi there!',
 *     body:     'Hi there!'
 * );
 * 
 * ////////
 * 
 * System broadcast to all users:
 * 
 * $userModel = model(\CodeIgniter\Shield\Models\UserModel::class);
 * $userIds   = $userModel->findColumn('id') ?? [];
 * 
 * service('messageService')->systemMessage(
 *     $userIds,
 *     'System Notice',
 *     'We will be down for maintenance at 10:00 PM.'
 * );
 * 
 */


class Messages extends BaseController
{
    protected $messageService;

    public function __construct()
    {
        $this->messageService = service('messageService');
    }

    public function index()
    {
        return redirect()->to(route_to('messages.inbox'));
    }

    public function inbox()
    {
        $userId = auth()->id();

        return view('messages/inbox', [
            'title'    => 'Inbox',
            'messages' => $this->messageService->inbox($userId),
        ]);
    }

    public function outbox()
    {
        $userId = auth()->id();

        return view('messages/outbox', [
            'title'    => 'Outbox',
            'messages' => $this->messageService->outbox($userId),
        ]);
    }

    public function show(int $recipientId)
    {
        $userId = auth()->id();

        $row = $this->messageService->getRecipientRow($recipientId, $userId);
        if (! $row) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Message not found.');
        }

        // Mark read
        $this->messageService->markRead($recipientId, $userId);

        return view('messages/show', [
            'title'   => 'Message',
            'message' => $row,
        ]);
    }

    public function compose()
    {
        // Later: dropdown of users, etc.
        return view('messages/compose', [
            'title' => 'Compose Message',
        ]);
    }

    public function send(): ResponseInterface
    {
        $userId = auth()->id();

        $to      = $this->request->getPost('to');      // e.g. user id (single for now)
        $subject = (string) $this->request->getPost('subject');
        $body    = (string) $this->request->getPost('body');

        $rules = [
            'to'      => 'required|integer',
            'subject' => 'permit_empty|max_length[255]',
            'body'    => 'required|min_length[2]',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->messageService->send($userId, (int) $to, $subject, $body);

        return redirect()
            ->to(route_to('messages.outbox'))
            ->with('success', 'Message sent.');
    }

    public function delete(int $recipientId): ResponseInterface
    {
        $userId = auth()->id();

        $this->messageService->deleteForUser($recipientId, $userId);

        return redirect()
            ->back()
            ->with('success', 'Message deleted.');
    }

    public function unreadCount()
    {
        $user = auth()->user();
        if (!$user) {
            return $this->response->setJSON(['count' => 0]);
        }

        $service = service('messageService');

        return $this->response->setJSON([
            'count' => $service->getUnreadCount($user->id)
        ]);
    }

    public function inboxPreview()
    {
        $service = service('messageService');
        $userId = auth()->id();

        return $this->response->setJSON([
            'success' => true,
            'data'    => $service->getInboxPreview($userId)
        ]);
    }
}
