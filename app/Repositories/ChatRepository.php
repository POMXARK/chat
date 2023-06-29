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

    public function loadMessages(array $dto)
    {
        $builder = Message::select(DB::raw('DATE_FORMAT(updated_at, "%d.%m.%Y %H:%i") as updated, stmt_id, text, from_user_id, to_user_id, created_at'));

        if ($stmtId = $dto['stmt']) {
            $builder->where('stmt_id', $stmtId);
        }
        if ($from = $dto['from']) {
            $builder->where('from_user_id', $from);
        }
        if ($to = $dto['to']) {
            $builder->where('to_user_id', $to);
        }

        if ($dto['from'] && $dto['to']) {
            $second = Message::select(DB::raw('DATE_FORMAT(updated_at, "%d.%m.%Y %H:%i") as updated_at, stmt_id, text, from_user_id, to_user_id, created_at'));

            if ($stmtId = $dto['stmt']) {
                $second->where('stmt_id', $stmtId);
            }
            if ($from = $dto['from']) {
                $second->where('to_user_id', $from);
            }
            if ($to = $dto['to']) {
                $second->where('from_user_id', $to);
            }

            $builder->union($second);
        }

        return $builder->orderBy('created_at')->get();//self::getSqlWithBindings($builder);
    }
}
