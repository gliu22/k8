<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::with('users')
            ->where('is_active', true)
            ->paginate(15);

        return response()->json($organizations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:organizations',
            'description' => 'nullable|string',
        ]);

        $organization = Organization::create($validated);

        return response()->json($organization, 201);
    }

    public function show(Organization $organization)
    {
        return response()->json(
            $organization->load(['users', 'projects'])
        );
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:organizations,slug,' . $organization->id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $organization->update($validated);

        return response()->json($organization);
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully',
        ]);
    }
}