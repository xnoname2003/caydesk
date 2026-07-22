<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole('administrator')) {
            return response()->json(['message' => 'Unauthorized action. Only administrators can access this resource.'], 403);
        }

        $users = User::with(['roles:id,name', 'team:id,name'])->latest()->paginate(15);

        return response()->json([
            'message' => 'Successfully retrieved user list.',
            'data' => $users
        ]);
    }

    public function showProfile(Request $request)
    {
        $user = $request->user()->load(['roles:id,name', 'team:id,name']);

        return response()->json([
            'message' => 'Profile data retrieved successfully.',
            'data' => $user
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $isDirty = $user->isDirty(); 
        
        $oldAttributes = $user->getOriginal(); 
        
        $user->save();

        if ($isDirty) {
            $newAttributes = $user->getChanges();

            $log = activity()
                ->performedOn($user)
                ->causedBy($user)
                ->event('updated')
                ->log('Profile has been updated');
            
            $log->attribute_changes = [
                'old' => array_intersect_key($oldAttributes, $newAttributes),
                'attributes' => $newAttributes,
            ];
            $log->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $user
        ]);
    }
}
