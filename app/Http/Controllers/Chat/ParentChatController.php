<?php

namespace App\Http\Controllers\Chat;

use App\Helpers\FileManager;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageDocument;
use App\Models\User;
use ExpressCredit\Models\Offer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

class ParentChatController extends Controller
{
    /**
     * Скачивание документа из сообщения в чате.
     *
     * @param int $documentId
     *
     * @return BinaryFileResponse
     */
    public function downloadDocument(int $documentId): BinaryFileResponse
    {
        $error404 = __('errors.document.not_found');
        /** @var User $user */
        $user = auth()->user();

        try {
            /** @var MessageDocument $document */
            $document = MessageDocument::findOrFail($documentId);
            $params = Message::getInfoByDocId($document);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException($error404);
        }

        $storage = Storage::disk('documents');

        if (!$storage->exists($document->path)) {
            abort(404, $error404);
        }

        $stmt = $document->message->subject;

        // Проверка доступа к файлу.
        switch(true) {
            case $stmt instanceof Offer:
                if (!$user->can('read', $stmt)) {
                    abort(404);
                }
                break;
            case $user->isAgent():
                if (
                    (
                        stripos($params['type'], 'agent') === false
                        && stripos($params['type'], 'client') === false
                    )
                    || !$this->agent->hasClient($params['inn'])
                ) {
                    abort(404, $error404);
                }
                break;
            case $user->isBank():
                if (stripos($params['type'], 'bank') === false
                    || !in_array($params['bank_user_id'], $user->myrole->getUsers()->pluck('id')->all(), false)
                ) {
                    abort(404, $error404);
                }
                break;
            case $user->isClient():
                if (
                    $params['from_user_id'] !== $user->id
                    && $params['to_user_id'] !== $user->id
                    && !($params['client_user_id'] === $user->id
                    && stripos($params['type'], 'client') !== false)
                ) {
                    abort(404, $error404);
                }
                break;
        }

        return response()->download($storage->path($document->path), $document->name);
    }
}
