<?php

namespace App\Repositories;

use App\Interfaces\ChatRepositoryInterface;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChatRepository implements ChatRepositoryInterface
{
    public function postMessage(array $dto): bool|JsonResponse
    {
        DB::beginTransaction();
        try {
            $newMessage = Message::create([
                'text'         => $dto['text'],
                'from_user_id' => $dto['from'],
                'to_user_id'   => $dto['to'],
                'stmt_id'    => $dto['stmt'],
                'created_at'   => Carbon::now()
            ]);

            // TODO: сделать загрузку файлов
//            if ($dto['files']) {
//                $this->uploadFiles($dto['files'], $newMessage->id, auth()->id());
//            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'msg' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return true;
    }

    public function loadMessages()
    {
        // TODO: Implement loadMessages() method.
    }
}
