<?php
declare(strict_types=1);

function initializeSession(): void
{
    session_start();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function redirect(string $message = '', array $parameters = [], string $flashType = 'success'): never
{
    if ($message !== '') {
        storeFlashMessage($message, $flashType);
    }
    header('Location: index.php' . ($parameters === [] ? '' : '?' . http_build_query($parameters)));
    exit;
}

function storeFlashMessage(string $message, string $type = 'success'): void
{
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type === 'error' ? 'error' : 'success',
    ];
}

/** @return array{message: string, type: 'error'|'success'}|null */
function pullFlashMessage(): ?array
{
    $flashMessage = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);

    if (!is_array($flashMessage) || !is_string($flashMessage['message'] ?? null)) {
        return null;
    }

    return [
        'message' => $flashMessage['message'],
        'type' => ($flashMessage['type'] ?? null) === 'error' ? 'error' : 'success',
    ];
}

/** @return array{category?: string} */
function returnFilterParameters(): array
{
    $category = $_POST['return_category'] ?? '';
    if ($category === 'none') {
        return ['category' => 'none'];
    }
    $categoryId = requestPositiveInt('return_category', $_POST);
    return $categoryId === null ? [] : ['category' => (string) $categoryId];
}

function validateCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('不正なリクエストです。');
    }
}

function requestPositiveInt(string $key, array $source): ?int
{
    $value = $source[$key] ?? null;
    if (!is_string($value) && !is_int($value)) {
        return null;
    }
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return is_int($id) ? $id : null;
}

function requiredText(string $key, string $label, int $maximumLength): string
{
    $text = trim((string) ($_POST[$key] ?? ''));
    if ($text === '') {
        redirect($label . 'を入力してください。', [], 'error');
    }
    if (!isWithinMaximumLength($text, $maximumLength)) {
        redirect($label . 'は' . $maximumLength . '文字以内で入力してください。', [], 'error');
    }
    return $text;
}

function isWithinMaximumLength(string $text, int $maximumLength): bool
{
    return mb_strlen($text) <= $maximumLength;
}

function requestCategoryId(): ?int
{
    $value = $_POST['category_id'] ?? '';
    if ($value === '') {
        return null;
    }
    $id = requestPositiveInt('category_id', $_POST);
    if ($id === null || findCategory($id) === null) {
        redirect('選択したカテゴリが見つかりません。', [], 'error');
    }
    return $id;
}
