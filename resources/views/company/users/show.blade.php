<x-default-layout>
    @section('title')
        {{ $user->first_name }} {{ $user->last_name }}
    @endsection

    <div class="card">
        <div class="card-body">
            <div class="pb-4 fs-6">
                <div class="fw-bold">Email</div>
                <div class="text-gray-600">{{ $user->email }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Username</div>
                <div class="text-gray-600">{{ $user->username }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">First Name</div>
                <div class="text-gray-600">{{ $user->first_name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Last Name</div>
                <div class="text-gray-600">{{ $user->last_name }}</div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Role</div>
                <div class="text-gray-600">
                    @php
                        $roles = $user->getRoleNames()->toArray();
                    @endphp
                    {{ implode(', ', $roles) }}
                </div>
            </div>

            <div class="pb-4 fs-6">
                <div class="fw-bold">Status</div>
                <div class="text-gray-600">
                    @if($user->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="pb-1 fs-6 mb-2">
                <div class="fw-bold">Branches</div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800">
                                <th>Branch Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->activeBranches as $branch)
                                <tr>
                                    <td>{{ $branch->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
