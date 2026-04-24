<section class="hero">
    <div class="hero__panel">
        <div class="hero__emblem" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        <p class="eyebrow">Система абонемента</p>
        <h1>Либрари</h1>
        <p class="hero__lead">
            Веб-интерфейс читального зала: каталог, поиск по экземплярам, электронные издания,
            бронирования и рабочее место библиотекаря.
        </p>

        <div class="role-grid">
            <a class="role-card <?= ($preferredRole ?? '') === 'reader' ? 'role-card--accent' : '' ?>" href="<?= app()->route->getUrl('/login?role=reader') ?>">
                <span class="role-card__icon">Ч</span>
                <strong>Я читатель</strong>
                <p>Каталог, электронные книги, мои брони и продление без очереди.</p>
            </a>

            <a class="role-card <?= ($preferredRole ?? '') === 'librarian' ? 'role-card--accent' : '' ?>" href="<?= app()->route->getUrl('/login?role=librarian') ?>">
                <span class="role-card__icon">Б</span>
                <strong>Я библиотекарь</strong>
                <p>Хранилище экземпляров, QR/штрих-код поиск и обработка запросов читателей.</p>
            </a>
        </div>
    </div>
</section>
