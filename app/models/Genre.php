<?php
declare(strict_types=1);

class Genre
{
    public static function all(): array
    {
        return getPDO()->query('SELECT g.id, g.name, COUNT(mg.movie_id) AS movie_count FROM genres g
            LEFT JOIN movie_genres mg ON mg.genre_id = g.id GROUP BY g.id ORDER BY g.name')->fetchAll();
    }

    public static function validateName(string $name, int $ignoreId = 0): ?string
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 50) return 'Tên thể loại bắt buộc, tối đa 50 ký tự.';
        $st = getPDO()->prepare('SELECT COUNT(*) FROM genres WHERE lower(name) = lower(?) AND id != ?');
        $st->execute([$name, $ignoreId]);
        return (int)$st->fetchColumn() > 0 ? 'Tên thể loại đã tồn tại.' : null;
    }

    public static function create(string $name): int
    {
        $st = getPDO()->prepare('INSERT INTO genres (name) VALUES (?)');
        $st->execute([trim($name)]);
        return (int)getPDO()->lastInsertId();
    }

    public static function update(int $id, string $name): void
    {
        $st = getPDO()->prepare('UPDATE genres SET name = ? WHERE id = ?');
        $st->execute([trim($name), $id]);
    }

    public static function delete(int $id): void
    {
        $st = getPDO()->prepare('SELECT COUNT(*) FROM movie_genres WHERE genre_id = ?');
        $st->execute([$id]);
        if ((int)$st->fetchColumn() > 0) {
            throw new InvalidArgumentException('Thể loại đang được dùng, không thể xóa.');
        }
        getPDO()->prepare('DELETE FROM genres WHERE id = ?')->execute([$id]);
    }

    public static function existingIds(array $ids): array
    {
        if (!$ids) return [];
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $st = getPDO()->prepare("SELECT id FROM genres WHERE id IN ($marks)");
        $st->execute($ids);
        return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    }
}
