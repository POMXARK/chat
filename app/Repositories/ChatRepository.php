<?php

namespace App\Repositories;

use App\DTO\MessageDTO;
use App\Helpers\FileManager;
use App\Interfaces\ChatRepositoryInterface;
use App\Models\Message;
use App\Models\MessageDocument;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

class ChatRepository implements ChatRepositoryInterface
{
    public function postMessage(MessageDTO $message): bool|JsonResponse
    {
        DB::beginTransaction();
        try {
            $newMessage = Message::create([
                'text'         => $message->text,
                'from_user_id' => $message->from,
                'to_user_id'   => $message->to,
                'stmt_id'    => $message->stmt,
                'created_at'   => Carbon::now()
            ]);

            // TODO: сделать загрузку файлов
//            if ($message['files']) {
//                $this->uploadFiles($message['files'], $newMessage->id, auth()->id());
//            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'msg' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return true;
    }

    public function loadMessages(MessageDTO $message)
    {
        $builder = Message::select(DB::raw('DATE_FORMAT(updated_at, "%d.%m.%Y %H:%i") as updated, stmt_id, text, from_user_id, to_user_id, created_at'));

        if ($stmtId = $message->stmt) {
            $builder->where('stmt_id', $stmtId);
        }
        if ($from = $message->from) {
            $builder->where('from_user_id', $from);
        }
        if ($to = $message->to) {
            $builder->where('to_user_id', $to);
        }

        if ($message->from && $message->to) {
            $second = Message::select(DB::raw('DATE_FORMAT(updated_at, "%d.%m.%Y %H:%i") as updated_at, stmt_id, text, from_user_id, to_user_id, created_at'));

            if ($stmtId = $message->stmt) {
                $second->where('stmt_id', $stmtId);
            }
            if ($from = $message->from) {
                $second->where('to_user_id', $from);
            }
            if ($to = $message->to) {
                $second->where('from_user_id', $to);
            }

            $builder->union($second);
        }

        return $builder->orderBy('created_at')->get();//self::getSqlWithBindings($builder);
    }

    protected $chatRow = '';

    /**
     * Загрузка файлов для прикрепления к сообщниям в чате.
     *
     * @param     $files
     * @param int $messageId
     * @param int $userId
     *
     * @return bool
     * @throws \Exception
     */
    protected function uploadFiles($files, int $messageId, int $userId)
    {
        $storage = Storage::disk('documents');
        $uploadedPaths = [];

        FileManager::checkFilesSize($files);

        try {
            /** @var UploadedFile[] $files */
            foreach ($files as $file) {
                $filename = sprintf('%s.%s', Str::random(32), $file->getClientOriginalExtension());
                $filepath = 'client/' . $userId . '/comments/' . $filename;

                $commentFile = $storage->putFileAs('client/' . $userId . '/comments', $file, $filename);
                if (!$commentFile) {
                    throw new UploadException();
                }

                $uploadedPaths[] = $filepath;

                MessageDocument::create([
                    'message_id' => $messageId,
                    'path'       => $filepath,
                    'name'       => $file->getClientOriginalName(),
                    'extension'  => $file->getClientOriginalExtension(),
                ]);
            }
        } catch (Throwable $e) {
            foreach ($uploadedPaths as $path) {
                $storage->delete($path);
            }
            throw new UploadException($e->getMessage());
        }

        return true;
    }
}
