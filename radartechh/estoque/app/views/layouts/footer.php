<?php
/**
 * footer.php — Rodapé HTML (fechamento da página)
 * Incluir ao final de toda view interna.
 */
?>
    </main><!-- /page-content -->

    <!-- Rodapé da página -->
    <footer class="text-center py-3" style="color: var(--text-secondary); font-size: 0.75rem; border-top: 1px solid var(--border-color);">
      <?= e(APP_NAME) ?> &copy; <?= date('Y') ?> — v<?= APP_VERSION ?>
    </footer>

  </div><!-- /main-content -->
</div><!-- /app-wrapper -->

<!-- Bootstrap 5 JS Bundle (com Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmn1rBE9WiGp7wK42fByQOQn/E8u"
        crossorigin="anonymous"></script>

<!-- JS personalizado do sistema -->
<script src="<?= APP_URL ?>/assets/js/estoque.js"></script>

</body>
</html>
