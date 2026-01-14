@extends('layouts.admin-dashboard')

@section('title', 'Dashboard - ' . config('app.name', 'Laravel'))

@section('header-title', 'Dashboard')
@section('header-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')
<div class="mb-6">
    <h2 class="text-3xl font-bold mb-2" style="color: #111827;">Welcome to the System!</h2>
    <p class="text-sm" style="color: #6B7280;">Efficient and professional fulfillment management</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" style="border-left-color: #2563EB;">
        <h3 class="text-lg font-semibold mb-2" style="color: #111827;">Account Information</h3>
        <div class="space-y-2">
            <p class="text-sm">
                <span style="color: #6B7280;">Email:</span>
                <span class="font-medium" style="color: #111827;">{{ auth()->user()->email }}</span>
            </p>
            <p class="text-sm">
                <span style="color: #6B7280;">Role:</span>
                <span class="font-medium" style="color: #111827;">{{ auth()->user()->role ? auth()->user()->role->name : 'Not assigned' }}</span>
            </p>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" style="border-left-color: #2563EB;">
        <h3 class="text-lg font-semibold mb-2" style="color: #2563EB;">Administrator Access</h3>
        <p class="text-sm" style="color: #6B7280;">
            You have full access to the system with administrator privileges.
        </p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" style="border-left-color: #D1D5DB;">
        <h3 class="text-lg font-semibold mb-2" style="color: #111827;">System</h3>
        <p class="text-sm" style="color: #6B7280;">
            The fulfillment management system is operating normally.
        </p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-8">
    <h3 class="text-xl font-bold mb-6" style="color: #111827;">Overview</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #FFFFFF;">
            <div class="text-2xl font-bold mb-1" style="color: #111827;">0</div>
            <div class="text-sm" style="color: #6B7280;">Orders</div>
        </div>
        <div class="p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #FFFFFF;">
            <div class="text-2xl font-bold mb-1" style="color: #111827;">0</div>
            <div class="text-sm" style="color: #6B7280;">Products</div>
        </div>
        <div class="p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #FFFFFF;">
            <div class="text-2xl font-bold mb-1" style="color: #111827;">0</div>
            <div class="text-sm" style="color: #6B7280;">Inventory</div>
        </div>
        <div class="p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #FFFFFF;">
            <div class="text-2xl font-bold mb-1" style="color: #111827;">0</div>
            <div class="text-sm" style="color: #6B7280;">Users</div>
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'dashboard';
@endphp
