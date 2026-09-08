@extends('layouts.app')

@section('content')

<header class="bg-white border-b border-gray-200 px-8 py-5">
<h2 class="text-2xl font-bold text-gray-900">
                Users
            </h2>
<p class="text-sm text-gray-500">
                Manage system users and their roles
            </p>
</header><section class="p-8">
<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Total Users
                    </p>
<p class="text-3xl font-bold mt-2" id="usersTotal">
                        0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Administrators
                    </p>
<p class="text-3xl font-bold text-blue-600 mt-2" id="usersAdmins">
                        0
                    </p>
</div>
<div class="bg-white rounded-xl border p-6">
<p class="text-sm text-gray-500">
                        Other Users
                    </p>
<p class="text-3xl font-bold text-gray-600 mt-2" id="usersOthers">
                        0
                    </p>
</div>
</div>
<!-- Search -->
<div class="bg-white rounded-xl border p-5 mb-6">
<input class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-3" id="usersSearch" placeholder="Search users..." type="text"/>
</div>
<!-- Table -->
<div class="bg-white rounded-xl border overflow-hidden">
<div class="px-6 py-5 border-b">
<h3 class="text-lg font-bold">
                        System Users
                    </h3>
</div>
<div class="overflow-x-auto">
<table class="w-full">
<thead class="bg-gray-50 border-b">
<tr>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Name
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Email
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Role
                                </th>
<th class="text-left px-6 py-4 text-sm font-semibold">
                                    Created
                                </th>
</tr>
</thead>
<tbody id="usersTable">
<tr>
<td class="text-center px-6 py-10 text-gray-500" colspan="4">

                                    Loading users...

                                </td>
</tr>
</tbody>
</table>
</div>
</div>
</section>

@endsection
