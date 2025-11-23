<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:user,email,' . $user->user_id . ',user_id',
            'address_line' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_pic_path' => 'nullable|string|max:255',
        ];
        
        // Only validate phone regex if phone is provided and not empty
        if ($request->has('phone') && $request->filled('phone')) {
            $rules['phone'] = 'required|string|max:20|regex:/^\+62[0-9]{8,}$/';
        } else {
            $rules['phone'] = 'nullable|string|max:20';
        }

        $validated = $request->validate($rules, [
            'phone.regex' => 'Phone number must start with +62 and have at least 8 digits after the country code.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];
        
        // Handle phone - set to null if empty, otherwise use validated value
        if ($request->has('phone')) {
            $updateData['phone'] = $request->filled('phone') ? $validated['phone'] : null;
        }
        
        // Handle nullable fields
        $nullableFields = ['address_line', 'district', 'city', 'province', 'postal_code'];
        foreach ($nullableFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->filled($field) ? $validated[$field] : null;
            }
        }

        if ($request->hasFile('profile_pic')) {
            if ($user->profile_pic) {
                Storage::disk('public')->delete($user->profile_pic);
            }
            $path = $request->file('profile_pic')->store('profiles', 'public');
            $updateData['profile_pic'] = $path;
        } elseif ($request->has('profile_pic_path')) {
            // Use path from Next.js upload (saved to public/profile_pics)
            $updateData['profile_pic'] = $validated['profile_pic_path'];
        }

        $user->update($updateData);
        
        // Refresh the user model to get updated data
        $user->refresh();

        return response()->json($user);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Additional password complexity check
        $password = $validated['password'];
        $errors = [];
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one symbol.';
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => implode(' ', $errors)
            ], 400);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }
}
