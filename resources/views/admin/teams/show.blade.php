@extends('layouts.app')

@section('title', 'Team Details - ' . config('app.name', 'Laravel'))

@section('header-title', $team->name)
@section('header-subtitle', 'Team members and information')

@section('header-actions')
<a href="{{ route('admin.teams.edit', $team) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
    Edit Team
</a>
<a href="{{ route('admin.teams.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Teams
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Team Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-xl flex items-center justify-center font-bold text-white text-xl shadow-md" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $team->name }}</h2>
                @if($team->description)
                    <p class="text-gray-600 mb-4">{{ $team->description }}</p>
                @endif
                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span>{{ $team->users->count() }} {{ $team->users->count() === 1 ? 'member' : 'members' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Created {{ $team->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Team Members</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($team->users as $user)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-semibold text-white text-sm shadow-sm" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-gray-900">{{ $user->name }}</h4>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-sm text-gray-600">{{ $user->email }}</span>
                                @if($user->role)
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full" style="background-color: #DBEAFE; color: #2563EB;">
                                        {{ $user->role->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
                        View
                    </a>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <p class="text-sm text-gray-600">No members in this team yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'teams';
@endphp





