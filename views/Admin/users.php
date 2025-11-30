<h2 class="mb-4">User Management</h2>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Verified</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= \App\View::e($user['name']) ?></td>
                    <td><?= \App\View::e($user['email']) ?></td>
                    <td><span class="badge bg-info"><?= ucfirst($user['role']) ?></span></td>
                    <td><?= $user['is_verified'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' ?></td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

