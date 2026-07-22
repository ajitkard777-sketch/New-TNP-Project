        </div><!-- /.content-wrapper -->

        <!-- Professional Footer -->
        <footer class="app-footer">
            <div class="app-footer-inner">
                <div class="app-footer-left">
                    <span class="app-footer-brand">
                        <i class="fas fa-graduation-cap me-1"></i>
                        <strong>TPMS</strong>
                    </span>
                    <span class="app-footer-sep">|</span>
                    <span>Training &amp; Placement Management System</span>
                </div>
                <div class="app-footer-right">
                    <a href="<?= url('/' . ($currentRole ?? 'student') . '/dashboard') ?>">Dashboard</a>
                    <a href="<?= url('/' . ($currentRole ?? 'student') . '/notifications') ?>">Notifications</a>
                    <a href="<?= url('/logout') ?>">Logout</a>
                    <span class="app-footer-copy">&copy; <?= date('Y') ?> TPMS. All rights reserved.</span>
                </div>
            </div>
        </footer>

    </div><!-- /.main-content -->
</div><!-- /.app-wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- App JS -->
<script src="<?= asset('js/app.js') ?>"></script>

<?php if (isset($extraJs)): ?>
    <?php foreach ((array)$extraJs as $js): ?>
        <script src="<?= asset('js/' . $js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inlineJs)): ?>
<script><?= $inlineJs ?></script>
<?php endif; ?>

</body>
</html>
