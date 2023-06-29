<?php

namespace App\Http\Controllers\Chat;

use App\Interfaces\Statements\CompatibleWithChat;
use App\Models\Message;
use App\Repository\AlertRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Throwable;

class ClientChatController extends ParentChatController
{
    protected $chatRow = 'client.child_stmt.chat';

    /**
     * Добавление сообщения в чат
     *
     * @param Request $request
     * @param $childStmt
     * @return string
     */
    public function postMessage(Request $request): string
    {
        $text = trim($request->input('text', ''));

        $files = $request->file('files');

        if (!strlen($text) && !$files) {
            return response()->json(['status' => false, 'msg' => 'Пожалуйста, введите сообщение или добавьте файл!']);
        }

        DB::beginTransaction();
        try {
            $newMessage = Message::create([
                'text'         => $text,
                'from_user_id' => $request->input('from_user_id'),
                'to_user_id'   => $request->input('to_user_id'),
                'stmt_id'    => $request->input('stmt_id'),
                'created_at'   => Carbon::now()
            ]);

            if ($files) {
                $this->uploadFiles($files, $newMessage->id, auth()->id());
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'msg' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(['status' => true]);
    }

    /**
     * Удаление сообщения из чата
     * У клиента нет удаления сообщений, но может появиться (пока метод не используется)
     *
     * @param                    $messageId
     */
    public function removeMessage($childStmt, $messageId)
    {
//        $message = Message::find($messageId);
//        if ($message->from_user_id == auth()->user()->id) {
//            $status = Message::remove($messageId);
//            return response()->json(['status' => $status]);
//        } else {
//            return response()->json(['status' => false]);
//        }
    }

    /**
     * Загрузка сообщений в чат (обновление сообщений)
     *
     *
     * @return string
     */
    public function loadMessages(Request $request)
    {
        $builder = Message::select(DB::raw('DATE_FORMAT(updated_at, "%d.%m.%Y %H:%i") as updated, stmt_id, text, from_user_id, to_user_id, created_at'));

        if ($stmtId = $request->input('stmt')) {
            $builder->where('stmt_id', $stmtId);
        }
        if ($from = $request->input('from')) {
            $builder->where('from_user_id', $from);
        }
        if ($to = $request->input('to')) {
            $builder->where('to_user_id', $to);
        }

        if ($request->input('from') && $request->input('to')) {
            $second = Message::select(DB::raw('DATE_FORMAT(updated_at, "%d.%m.%Y %H:%i") as updated_at, stmt_id, text, from_user_id, to_user_id, created_at'));

            if ($stmtId = $request->input('stmt')) {
                $second->where('stmt_id', $stmtId);
            }
            if ($from = $request->input('from')) {
                $second->where('to_user_id', $from);
            }
            if ($to = $request->input('to')) {
                $second->where('from_user_id', $to);
            }

            $builder->union($second);
        }

        return $builder->orderBy('created_at')->get();//self::getSqlWithBindings($builder);
    }

    public static function getSqlWithBindings($query): string
    {
        $bindings = $query->getBindings();

        return preg_replace_callback('/\?/', function ($match) use (&$bindings, $query) {
            return $query->getConnection()->getPdo()->quote(array_shift($bindings));
        }, $query->toSql());
    }

}
