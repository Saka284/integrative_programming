<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        return response()->json(Post::all());
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string|max:100',
                'status' => 'required|in:published,draft',
                'content' => 'required|string',
            ]);

            $user = Auth::user();
            $data = $request->only(['title', 'status', 'content']);

            // Penulis otomatis pakai id sendiri
            if ($user->role === 'penulis') {
                $data['user_id'] = $user->id;
            } else if ($user->role === 'admin') {
                $this->validate($request, ['user_id' => 'required|exists:users,id']);
                $data['user_id'] = $request->user_id;
            }

            $post = Post::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Post berhasil disimpan',
                'data' => $post
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
                'message' => 'Terjadi kesalahan saat menyimpan post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post tidak ditemukan'
            ], 404);
        }

        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string|max:100',
                'status' => 'required|in:published,draft',
                'content' => 'required|string',
            ]);

            $user = Auth::user(); 
            $post = Post::find($id);

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post tidak ditemukan'
                ], 404);
            }

            // Penulis hanya bisa edit post miliknya sendiri
            if ($user->role === 'penulis' && $post->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengedit post ini'
                ], 403);
            }

            // Hanya admin boleh mengubah user_id
            $updateData = $request->only(['title', 'status', 'content']);
            if ($user->role === 'admin' && $request->has('user_id')) {
                $this->validate($request, [
                    'user_id' => 'exists:users,id'
                ]);
                $updateData['user_id'] = $request->user_id;
            }

            $post->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Post berhasil diperbarui',
                'data' => $post
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
                'message' => 'Terjadi kesalahan saat memperbarui post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePartial(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'title' => 'sometimes|required|string|max:100',
                'status' => 'sometimes|required|in:published,draft',
                'content' => 'sometimes|required|string',
                'user_id' => 'sometimes|required|exists:users,id'
            ]);

            $user = Auth::user();
            $post = Post::find($id);

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post tidak ditemukan'
                ], 404);
            }

            if ($user->role === 'penulis' && $post->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk mengedit post ini'], 403);
            }

            $post->update($request->only(['title', 'status', 'content']));

            return response()->json([
                'success' => true,
                'message' => 'Post berhasil diupdate sebagian',
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post tidak ditemukan'
                ], 404);
            }

            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus post',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
