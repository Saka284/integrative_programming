<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

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
                'user_id' => 'required|exists:users,id'
            ]);

            $post = Post::create($request->all());

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
                'user_id' => 'required|exists:users,id'
            ]);

            $post = Post::find($id);

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post tidak ditemukan'
                ], 404);
            }

            $post->update($request->all());

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

            $post = Post::find($id);
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post tidak ditemukan'
                ], 404);
            }

            $post->update($request->only(['title', 'status', 'content', 'user_id']));

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
