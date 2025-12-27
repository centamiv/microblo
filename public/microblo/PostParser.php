<?php
if (!defined('MICROBLO_APP') && !defined('MICROBLO_ADMIN')) {
    http_response_code(403);
    exit;
}

class PostParser
{
    /**
     * Parse a markdown file to extract metadata and content.
     *
     * @param string $filePath Absolute path to the markdown file.
     * @return array|null Associative array with title, content, markdown, and date, or null if file not found.
     */
    public function parse(string $filePath): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        $title = null;
        $body = $content;
        $metaDescription = '';

        // Extract Front Matter
        $pattern = '/^---\s*\n(.*?)\n---\s*\n(.*)$/s';
        if (preg_match($pattern, $content, $matches)) {
            $frontMatterRaw = $matches[1];
            $body = $matches[2];
            $meta = $this->parseYamlSimple($frontMatterRaw);
            if (!empty($meta['description'])) {
                $metaDescription = $meta['description'];
            }
        }

        if (!class_exists('Parsedown')) {
            require_once __DIR__ . '/Parsedown.php';
        }
        $Parsedown = new \Parsedown();
        $htmlContent = $Parsedown->text($body);

        // Use H1 from content as title
        if (preg_match('/<h1>(.*?)<\/h1>/i', $htmlContent, $h1Matches)) {
            $title = strip_tags($h1Matches[1]);
        }

        // Generate date from filename, also generate title as fallback
        $basename = basename($filePath, '.md');
        $date = null;

        if (preg_match('/^(\d{4}-\d{2}-\d{2})-(.*)$/', $basename, $dateMatches)) {
            $date = $dateMatches[1];
            $rest = $dateMatches[2];
        } else {
            $rest = $basename;
        }

        if (!$title) {
            if (preg_match('/^(.*)-([a-z]{2})$/', $rest, $slugMatches)) {
                $title = ucfirst(str_replace('-', ' ', $slugMatches[1]));
            } else {
                $title = ucfirst(str_replace('-', ' ', $rest));
            }
        }

        return [
            'title' => $title,
            'content' => $htmlContent,
            'markdown' => $body,
            'date' => $date,
            'metaDescription' => $metaDescription
        ];
    }

    /**
     * Simple YAML parser for front matter.
     *
     * @param string $text Raw YAML string.
     * @return array Parsed key-value pairs.
     */
    private function parseYamlSimple(string $text): array
    {
        $lines = explode("\n", $text);
        $data = [];
        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$key, $val] = explode(':', $line, 2);
                $data[trim($key)] = trim($val);
            }
        }
        return $data;
    }
}
