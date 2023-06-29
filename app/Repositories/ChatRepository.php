<?php

namespace App\Repositories;

use App\DTO\MessageDTO;
use App\Interfaces\ChatRepositoryInterface;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
}
