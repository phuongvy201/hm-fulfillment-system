@extends('layouts.app')

@section('title', 'Create User - ' . config('app.name', 'Laravel'))

@section('header-title', 'Create New User')
@section('header-subtitle', 'Add a new user to the system')

@section('header-actions')
<a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Users
</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                <ul class="text-sm" style="color: #991B1B;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-semibold mb-2" style="color: #111827;">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        required 
                        autofocus
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold mb-2" style="color: #111827;">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold mb-2" style="color: #111827;">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                    <p class="mt-1 text-xs" style="color: #6B7280;">Minimum 8 characters</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold mb-2" style="color: #111827;">Confirm Password</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div>
                    <label for="role_id" class="block text-sm font-semibold mb-2" style="color: #111827;">Role</label>
                    <select 
                        id="role_id" 
                        name="role_id"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                        <option value="">Select a role (optional)</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="team_id" class="block text-sm font-semibold mb-2" style="color: #111827;">Team</label>
                    <select 
                        id="team_id" 
                        name="team_id"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                        <option value="">Individual (No Team)</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs" style="color: #6B7280;">Leave blank for individual customer</p>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'users';
@endphp
