<section class="auth-screen">
    <div class="auth-card">
        <div class="auth-card__brand" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        <p class="eyebrow"><?= ($preferredRole ?? 'reader') === 'librarian' ? 'Рабочее место библиотекаря' : 'Личный кабинет читателя' ?></p>
        <h1>Вход</h1>

        <?php if (!empty($message)): ?>
            <div class="inline-message inline-message--error"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></div>
        <?php endif; ?>

        <form class="auth-form" method="post">
            <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>">
            <label class="field">
                <span>Логин</span>
                <input type="text" name="login" placeholder="Введите логин" required>
            </label>

            <label class="field">
                <span>Пароль</span>
                <input type="password" name="password" placeholder="Введите пароль" required>
            </label>

            <button class="button button--block" type="submit">Войти</button>
        </form>

        <p class="auth-note">
            Нет аккаунта?
            <a href="<?= app()->route->getUrl('/signup') ?>">Зарегистрироваться</a>
        </p>
    </div>
</section>
