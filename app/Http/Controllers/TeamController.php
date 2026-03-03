<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Requests\AddMembersRequest;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with('leader', 'members')->get();
        return response()->json($teams);
    }

    public function store(StoreTeamRequest $request)
    {
        $team = Team::create($request->validated());

        if ($request->has('member_ids')) {
            $team->members()->attach($request->member_ids);
        }

        return response()->json($team->load('leader', 'members'), 201);
    }

    public function show($id)
    {
        $team = Team::with('leader', 'members', 'appointments')->findOrFail($id);
        return response()->json($team);
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        $team = Team::findOrFail($id);
        $team->update($request->validated());
        return response()->json($team->load('leader', 'members'));
    }

    public function destroy($id)
    {
        $team = Team::findOrFail($id);
        $team->members()->detach();
        $team->delete();
        return response()->json(['message' => 'Equipo eliminado']);
    }

    public function addMembers(AddMembersRequest $request, $id)
    {
        $team = Team::findOrFail($id);
        $team->members()->syncWithoutDetaching($request->member_ids);
        return response()->json($team->load('members'));
    }

    public function removeMember($id, $memberId)
    {
        $team = Team::findOrFail($id);
        $team->members()->detach($memberId);
        return response()->json(['message' => 'Miembro eliminado del equipo']);
    }
}