<section class="auth-screen">
    <div class="auth-card auth-card--wide">
        <div class="auth-card__brand" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        <p class="eyebrow">Регистрация читателя</p>
        <h1>Новый аккаунт</h1>

        <?php if (!empty($message)): ?>
            <div class="inline-message inline-message--error"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></div>
        <?php endif; ?>

        <form class="auth-form" method="post">
            <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>">
            <label class="field">
                <span>Как вас зовут?</span>
                <input type="text" name="name" placeholder="Иванов Иван Иванович" required>
            </label>

            <label class="field">
                <span>Логин</span>
                <input type="text" name="login" placeholder="reader_ivan" required>
            </label>

            <label class="field">
                <span>Пароль</span>
                <input type="password" name="password" placeholder="Не менее 6 символов" required>
            </label>

            <button class="button button--block" type="submit">Зарегистрироваться</button>
        </form>

        <p class="auth-note">
            Уже есть аккаунт?
            <a href="<?= app()->route->getUrl('/login') ?>">Войти</a>
        </p>
    </div>
</section>
