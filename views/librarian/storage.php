<section class="surface">
    <div class="section-head">
        <div>
            <p class="eyebrow">Рабочее место библиотекаря</p>
            <h1>Хранилище книг</h1>
        </div>
        <p class="section-note">Поиск по названию, автору, инвентарному номеру, QR-коду и штрих-коду экземпляра.</p>
    </div>

    <section class="booking-section">
        <h2>Добавить издание</h2>
        <form class="auth-form" method="post" action="<?= app()->route->getUrl('/storage/books') ?>" enctype="multipart/form-data">
            <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>">
            <label class="field">
                <span>Название книги</span>
                <input type="text" name="name" placeholder="Например, Мастер и Маргарита" required>
            </label>

            <label class="field">
                <span>Автор</span>
                <input type="text" name="author" placeholder="Фамилия Имя" required>
            </label>

            <label class="field">
                <span>Описание</span>
                <input type="text" name="description" placeholder="Краткое описание издания">
            </label>

            <label class="field">
                <span>Инвентарный номер</span>
                <input type="text" name="inventory_number" placeholder="INV-0101" required>
            </label>

            <label class="field">
                <span>Место хранения</span>
                <select name="storage_place_id" required>
                    <option value="">Выберите место хранения</option>
                    <?php foreach ($storagePlaces as $place): ?>
                        <option value="<?= (int) $place['id'] ?>">
                            <?= htmlspecialchars($place['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Обложка</span>
                <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/*" required>
            </label>

            <label class="field">
                <span>Электронная версия</span>
                <input type="file" name="digital_file" accept=".pdf,.txt,.epub" required>
            </label>

            <button class="button" type="submit">Загрузить книгу</button>
        </form>
    </section>

    <form class="toolbar" method="get">
        <label class="search-field">
            <span class="search-field__icon">⌕</span>
            <input type="search" name="q" value="<?= htmlspecialchars($storageSearch ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" placeholder="Поиск по названию, автору, QR или штрих-коду...">
        </label>

        <label class="select-field">
            <select name="status">
                <option value="">Все статусы</option>
                <?php foreach ($storageStatuses as $item): ?>
                    <option value="<?= htmlspecialchars($item, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" <?= ($storageStatus ?? '') === $item ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button class="button button--ghost" type="submit">Применить</button>
    </form>

    <div class="table-shell">
        <table class="data-table">
            <thead>
            <tr>
                <th>Название книги</th>
                <th>Автор</th>
                <th>Инвентарный номер</th>
                <th>Место хранения</th>
                <th>Статус</th>
                <th>Штрих-код</th>
                <th>QR-код</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($storageRows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></td>
                    <td><?= htmlspecialchars($row['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></td>
                    <td><?= htmlspecialchars($row['inventory_number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></td>
                    <td><?= htmlspecialchars($row['storage_place'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($row['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span></td>
                    <td><?= htmlspecialchars($row['barcode'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></td>
                    <td><?= htmlspecialchars($row['qr_code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($storageRows)): ?>
        <div class="empty-state">
            <h3>Экземпляры не найдены</h3>
            <p>Проверьте фильтры или убедитесь, что экземпляры книг загружены в базу данных.</p>
        </div>
    <?php endif; ?>
</section>
