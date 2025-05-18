<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index()
    {
        return response()->json(Comment::all());
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'comment' => 'required|string|max:250',
                'post_id' => 'required|exists:posts,id',
            ]);

            $user = Auth::user();
            $data = $request->only(['comment', 'post_id']);

            if ($user->role === 'penulis') {
                $data['user_id'] = $user->id;
            } else if ($user->role === 'admin') {
                $this->validate($request, ['user_id' => 'required|exists:users,id']);
                $data['user_id'] = $request->user_id;
            }

            $comment = Comment::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil disimpan',
                'data' => $comment
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan komentar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Komentar tidak ditemukan'
            ], 404);
        }

        return response()->json($comment);
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'comment' => 'required|string|max:250',
                'post_id' => 'required|exists:posts,id',
            ]);

            $user = Auth::user();
            $comment = Comment::find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komentar tidak ditemukan'
                ], 404);
            }

            if ($user->role === 'penulis' && $comment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki izin untuk mengedit komentar ini'
                ], 403);
            }

            $updateData = $request->only(['comment', 'post_id']);

            if ($user->role === 'admin' && $request->has('user_id')) {
                $this->validate($request, ['user_id' => 'exists:users,id']);
                $updateData['user_id'] = $request->user_id;
            }

            $comment->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil diperbarui',
                'data' => $comment
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui komentar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePartial(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'comment' => 'sometimes|required|string|max:250',
                'post_id' => 'sometimes|required|exists:posts,id',
                'user_id' => 'sometimes|required|exists:users,id',
            ]);

            $user = Auth::user();
            $comment = Comment::find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komentar tidak ditemukan'
                ], 404);
            }

            if ($user->role === 'penulis' && $comment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki izin untuk mengedit komentar ini'
                ], 403);
            }

            $updateData = $request->only(['comment', 'post_id']);

            if ($user->role === 'admin' && $request->has('user_id')) {
                $updateData['user_id'] = $request->user_id;
            }

            $comment->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil diperbarui sebagian',
                'data' => $comment
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui komentar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $comment = Comment::find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komentar tidak ditemukan'
                ], 404);
            }

            if ($user->role === 'penulis' && $comment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki izin untuk menghapus komentar ini'
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus komentar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
