<section class="surface">
    <div class="section-head">
        <div>
            <p class="eyebrow">Рабочее место библиотекаря</p>
            <h1>Хранилище книг</h1>
        </div>
        <p class="section-note">Поиск по названию, автору, инвентарному номеру, QR-коду и штрих-коду экземпляра.</p>
    </div>

    <form class="toolbar" method="get">
        <label class="search-field">
            <span class="search-field__icon">⌕</span>
            <input type="search" name="q" value="<?= htmlspecialchars($storageSearch ?? '') ?>" placeholder="Поиск по названию, автору, QR или штрих-коду...">
        </label>

        <label class="select-field">
            <select name="status">
                <option value="">Все статусы</option>
                <?php foreach ($storageStatuses as $item): ?>
                    <option value="<?= htmlspecialchars($item) ?>" <?= ($storageStatus ?? '') === $item ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item) ?>
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
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['author']) ?></td>
                    <td><?= htmlspecialchars($row['inventory_number']) ?></td>
                    <td><?= htmlspecialchars($row['storage_place']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td><?= htmlspecialchars($row['barcode']) ?></td>
                    <td><?= htmlspecialchars($row['qr_code']) ?></td>
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
