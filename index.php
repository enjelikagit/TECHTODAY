
<?php
require_once __DIR__ . '/config/config.php';

/*
|--------------------------------------------------------------------------
| TECHTODAY - TECHNOLOGY & DIGITAL MEDIA
|--------------------------------------------------------------------------
| NewsAPI + cURL
| Maksimal 10 artikel termasuk Featured Story.
*/

// Masukkan API Key.
$apiKey = 'RAHASIA';

$categories = [
    'all' => 'Semua Berita',
    'ai' => 'Artificial Intelligence',
    'gadgets' => 'Gadget',
    'cybersecurity' => 'Cybersecurity',
    'software' => 'Software',
    'startups' => 'Startup'
];

$category = strtolower(trim($_GET['category'] ?? 'all'));
$search = trim($_GET['q'] ?? '');

if (!isset($categories[$category])) {
    $category = 'all';
}

/*
|--------------------------------------------------------------------------
| KEYWORD KATEGORI
|--------------------------------------------------------------------------
*/

$categoryKeywords = [
    'ai' => [
        'artificial intelligence',
        'machine learning',
        'chatgpt',
        'openai',
        'generative ai',
        'robotics',
        'google gemini',
        'deep learning',
        'ai model'
    ],

    'gadgets' => [
        'smartphone',
        'iphone',
        'android',
        'laptop',
        'smartwatch',
        'tablet',
        'gadget',
        'processor',
        'samsung galaxy',
        'computer',
        'mobile device'
    ],

    'cybersecurity' => [
        'cybersecurity',
        'cyber security',
        'cyber attack',
        'hacking',
        'data breach',
        'malware',
        'ransomware',
        'privacy',
        'security vulnerability',
        'phishing',
        'data protection'
    ],

    'software' => [
        'software',
        'application',
        'app update',
        'operating system',
        'windows',
        'linux',
        'android update',
        'developer',
        'programming',
        'microsoft',
        'google chrome',
        'browser',
        'cloud computing'
    ],

    'startups' => [
        'startup',
        'start-up',
        'venture capital',
        'funding round',
        'technology company',
        'tech company',
        'investment',
        'tech founder',
        'technology business'
    ]
];

/*
|--------------------------------------------------------------------------
| FUNGSI BANTU
|--------------------------------------------------------------------------
*/

function techText($article)
{
    return strtolower(
        ($article['title'] ?? '') . ' ' .
        ($article['description'] ?? '') . ' ' .
        ($article['content'] ?? '')
    );
}

function containsKeyword($text, $keywords)
{
    foreach ($keywords as $keyword) {

        $keyword = trim($keyword);

        if ($keyword === '') {
            continue;
        }

        if (strlen($keyword) <= 3) {

            if (preg_match(
                '/\b' . preg_quote($keyword, '/') . '\b/i',
                $text
            )) {
                return true;
            }

        } elseif (stripos($text, $keyword) !== false) {

            return true;
        }
    }

    return false;
}

function articleCategory($article)
{
    global $categoryKeywords;

    $text = techText($article);

    foreach ($categoryKeywords as $key => $keywords) {

        if (containsKeyword($text, $keywords)) {
            return $key;
        }
    }

    return 'technology';
}

function formatDate($date)
{
    if (!$date) {
        return 'Tanggal tidak tersedia';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return 'Tanggal tidak tersedia';
    }

    return date('d M Y', $timestamp);
}

function safeImage($url)
{
    if (!$url) {
        return false;
    }

    return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
}

/*
|--------------------------------------------------------------------------
| NEWSAPI
|--------------------------------------------------------------------------
*/

$technologyQuery = 'technology OR digital OR artificial intelligence OR cybersecurity OR smartphone OR software OR startup OR gadgets';

if ($search !== '') {

    $query = '(' . $search . ') AND (technology OR digital OR software OR AI OR smartphone OR cybersecurity OR startup)';

} else {

    $query = $technologyQuery;
}

$params = [
    'q' => $query,
    'searchIn' => 'title,description',
    'language' => 'en',
    'sortBy' => 'publishedAt',
    'pageSize' => 100,
    'apiKey' => $apiKey
];

$url = 'https://newsapi.org/v2/everything?' . http_build_query($params);

$apiResult = http_request_get($url);

$articles = [];
$errorMessage = '';

if (!empty($apiResult['success'])) {

    $responseData = json_decode($apiResult['data'], true);

    if (
        isset($responseData['articles']) &&
        is_array($responseData['articles'])
    ) {
        $articles = $responseData['articles'];
    }

} else {

    $errorMessage = $apiResult['message']
        ?? 'Tidak dapat mengambil berita dari NewsAPI.';
}

/*
|--------------------------------------------------------------------------
| FILTER ARTIKEL TEKNOLOGI
|--------------------------------------------------------------------------
*/

$technologyKeywords = [
    'technology',
    'tech industry',
    'digital',
    'artificial intelligence',
    'AI',
    'machine learning',
    'software',
    'smartphone',
    'cybersecurity',
    'cyber security',
    'cyber attack',
    'gadget',
    'startup',
    'computer',
    'internet',
    'robot',
    'data',
    'chip',
    'processor',
    'app',
    'programming',
    'cloud computing',
    'semiconductor',
    'operating system',
    'mobile device',
    'technology company'
];

$excludedKeywords = [
    'football',
    'soccer',
    'basketball',
    'tennis',
    'celebrity',
    'box office',
    'fashion week',
    'weather forecast',
    'horoscope',
    'recipe',
    'wedding',
    'movie review',
    'music festival',
    'royal family',
    'travel guide',
    'tourism'
];

$filteredArticles = [];

foreach ($articles as $article) {

    $text = techText($article);

    // Hanya artikel yang memiliki konteks teknologi.
    if (!containsKeyword($text, $technologyKeywords)) {
        continue;
    }

    // Hilangkan artikel yang tidak sesuai.
    if (containsKeyword($text, $excludedKeywords)) {
        continue;
    }

    // Filter kategori.
    if ($category !== 'all') {

        if (!containsKeyword($text, $categoryKeywords[$category])) {
            continue;
        }
    }

    $filteredArticles[] = $article;
}

/*
|--------------------------------------------------------------------------
| RANKING RELEVANSI
|--------------------------------------------------------------------------
*/

$priorityKeywords = [

    // Artificial Intelligence
    'artificial intelligence',
    'generative ai',
    'machine learning',
    'chatgpt',
    'openai',
    'google gemini',
    'deep learning',
    'robotics',

    // Cybersecurity
    'cybersecurity',
    'cyber security',
    'data breach',
    'ransomware',
    'malware',
    'data privacy',
    'phishing',
    'security vulnerability',

    // Gadgets
    'smartphone',
    'iphone',
    'android',
    'samsung galaxy',
    'gadget',
    'laptop',
    'processor',
    'semiconductor',
    'smartwatch',

    // Software
    'software',
    'operating system',
    'cloud computing',
    'programming',
    'application',
    'developer',

    // Startups
    'startup',
    'start-up',
    'startup funding',
    'venture capital',
    'funding round',
    'technology startup',
    'digital innovation'
];

foreach ($filteredArticles as &$article) {

    $title = strtolower($article['title'] ?? '');
    $description = strtolower($article['description'] ?? '');

    $score = 0;

    foreach ($priorityKeywords as $keyword) {

        if (stripos($title, $keyword) !== false) {
            $score += 5;
        }

        if (stripos($description, $keyword) !== false) {
            $score += 2;
        }
    }

    $article['_relevance_score'] = $score;
}

unset($article);

// Urutkan artikel berdasarkan skor tertinggi.
usort($filteredArticles, function ($a, $b) {

    $scoreA = $a['_relevance_score'] ?? 0;
    $scoreB = $b['_relevance_score'] ?? 0;

    if ($scoreA === $scoreB) {

        $dateA = strtotime($a['publishedAt'] ?? '') ?: 0;
        $dateB = strtotime($b['publishedAt'] ?? '') ?: 0;

        return $dateB <=> $dateA;
    }

    return $scoreB <=> $scoreA;
});

/*
|--------------------------------------------------------------------------
| BATAS MAKSIMAL 10 ARTIKEL
|--------------------------------------------------------------------------
*/

$filteredArticles = array_slice(
    $filteredArticles,
    0,
    10
);

$totalArticles = count($filteredArticles);

// Pisahkan setelah pembatasan 10 artikel.
$featuredArticle = $filteredArticles[0] ?? null;
$otherArticles = array_slice($filteredArticles, 1);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TECHTODAY - Teknologi dan Digital</title>

    <meta
        name="description"
        content="TECHTODAY menyajikan berita terbaru seputar teknologi, AI, gadget, cybersecurity, software, dan startup."
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="/the-genz-daily/assets/css/style.css?v=20261110"
    >

</head>

<body>

<!-- NAVBAR -->
<header class="navbar">

    <div class="container nav-container">

        <a href="index.php" class="brand">

            <span class="brand-icon"><i></i></span>

            <span>
                TECH<span class="brand-highlight">TODAY</span>
            </span>

        </a>

        <nav class="nav-links">

            <a
                href="index.php"
                class="<?= $category === 'all' ? 'active' : '' ?>"
            >Home</a>

            <a
                href="?category=ai"
                class="<?= $category === 'ai' ? 'active' : '' ?>"
            >AI</a>

            <a
                href="?category=gadgets"
                class="<?= $category === 'gadgets' ? 'active' : '' ?>"
            >Gadgets</a>

            <a
                href="?category=cybersecurity"
                class="<?= $category === 'cybersecurity' ? 'active' : '' ?>"
            >Cybersecurity</a>

            <a
                href="?category=software"
                class="<?= $category === 'software' ? 'active' : '' ?>"
            >Software</a>

            <a
                href="?category=startups"
                class="<?= $category === 'startups' ? 'active' : '' ?>"
            >Startups</a>

        </nav>

        <a href="#latest" class="nav-button">
            Explore News <span>↗</span>
        </a>

    </div>

</header>


<!-- HERO -->
<section class="hero">

    <div class="hero-grid"></div>

    <div class="container hero-content">

        <div class="hero-label">

            <span class="live-dot"></span>

            TECHNOLOGY & DIGITAL MEDIA

        </div>

        <h1>
            Stay Ahead in<br>
            the <span>Digital Era.</span>
        </h1>

        <p>
            Temukan perkembangan terbaru dunia teknologi,
            kecerdasan buatan, gadget, dan inovasi digital
            dalam satu tempat.
        </p>

        <div class="hero-actions">

            <a href="#latest" class="primary-button">
                Explore Latest News <span>→</span>
            </a>

            <a href="#topics" class="secondary-button">
                Explore Topics
            </a>

        </div>

        <div class="hero-bottom">

            <div class="hero-stat">
                <strong>01</strong>
                <span>Technology Focus</span>
            </div>

            <div class="hero-stat">
                <strong>05</strong>
                <span>News Categories</span>
            </div>

            <div class="hero-stat">
                <strong>24/7</strong>
                <span>Digital Updates</span>
            </div>

        </div>

    </div>

    <div class="hero-decoration">

        <div class="decoration-circle circle-one"></div>
        <div class="decoration-circle circle-two"></div>
        <div class="decoration-line"></div>

        <span class="decoration-text">
            TECHNOLOGY / INNOVATION / FUTURE
        </span>

    </div>

</section>


<!-- TOPICS -->
<section class="topics-section" id="topics">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="eyebrow">
                    EXPLORE BY CATEGORY
                </span>

                <h2>Discover Your Interests.</h2>

            </div>

            <p>
                Jelajahi berbagai topik teknologi
                yang sedang berkembang.
            </p>

        </div>

        <div class="topic-grid">

            <a href="?category=ai" class="topic-card topic-ai">

                <span class="topic-number">01</span>

                <div class="topic-icon">✦</div>

                <h3>Artificial Intelligence</h3>

                <p>
                    AI, machine learning, dan inovasi
                    kecerdasan buatan.
                </p>

                <span class="topic-arrow">↗</span>

            </a>


            <a href="?category=gadgets" class="topic-card topic-gadget">

                <span class="topic-number">02</span>

                <div class="topic-icon">⌘</div>

                <h3>Gadgets</h3>

                <p>
                    Smartphone, laptop, dan perangkat
                    teknologi terbaru.
                </p>

                <span class="topic-arrow">↗</span>

            </a>


            <a href="?category=cybersecurity" class="topic-card topic-security">

                <span class="topic-number">03</span>

                <div class="topic-icon">⌑</div>

                <h3>Cybersecurity</h3>

                <p>
                    Keamanan data, privasi,
                    dan ancaman digital.
                </p>

                <span class="topic-arrow">↗</span>

            </a>


            <a href="?category=software" class="topic-card topic-software">

                <span class="topic-number">04</span>

                <div class="topic-icon">⌨</div>

                <h3>Software</h3>

                <p>
                    Aplikasi, sistem operasi,
                    dan perkembangan software.
                </p>

                <span class="topic-arrow">↗</span>

            </a>


            <a href="?category=startups" class="topic-card topic-startup">

                <span class="topic-number">05</span>

                <div class="topic-icon">↗</div>

                <h3>Startups</h3>

                <p>
                    Inovasi bisnis digital
                    dan perusahaan teknologi.
                </p>

                <span class="topic-arrow">↗</span>

            </a>

        </div>

    </div>

</section>


<!-- LATEST NEWS -->
<section class="news-section" id="latest">

    <div class="container">

        <div class="section-heading news-heading">

            <div>

                <span class="eyebrow">
                    THE LATEST UPDATES
                </span>

                <h2>
                    <?= $category === 'all'
                        ? 'Latest Technology News.'
                        : htmlspecialchars($categories[$category]) . ' News.' ?>
                </h2>

                <p class="news-count">
                    Menampilkan <?= $totalArticles ?> dari maksimal 10 artikel
                </p>

            </div>


            <form
                action="index.php"
                method="GET"
                class="search-form"
            >

                <?php if ($category !== 'all'): ?>

                    <input
                        type="hidden"
                        name="category"
                        value="<?= htmlspecialchars($category) ?>"
                    >

                <?php endif; ?>


                <input
                    type="text"
                    name="q"
                    placeholder="Search technology news..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <button type="submit">⌕</button>

            </form>

        </div>


        <?php if ($errorMessage !== ''): ?>

            <div class="status-message error-message">

                <strong>Unable to load stories.</strong>

                <p>
                    <?= htmlspecialchars($errorMessage) ?>
                </p>

                <a href="index.php">Try again →</a>

            </div>


        <?php elseif (empty($filteredArticles)): ?>

            <div class="status-message">

                <div class="empty-icon">⌕</div>

                <h3>No articles found.</h3>

                <p>
                    Belum ada artikel yang sesuai dengan kategori
                    atau kata kunci tersebut. Coba kategori lain
                    atau gunakan kata pencarian berbeda.
                </p>

                <a href="index.php" class="primary-button">
                    View All News →
                </a>

            </div>


        <?php else: ?>


            <!-- FEATURED STORY -->
            <?php if ($featuredArticle): ?>

                <div class="featured-card">

                    <a
                        href="<?= htmlspecialchars($featuredArticle['url'] ?? '#') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="featured-image"
                    >

                        <?php if (safeImage($featuredArticle['urlToImage'] ?? '')): ?>

                            <img
                                src="<?= htmlspecialchars($featuredArticle['urlToImage']) ?>"
                                alt="<?= htmlspecialchars($featuredArticle['title'] ?? 'Technology news') ?>"
                                onerror="this.onerror=null;this.remove();this.parentElement.insertAdjacentHTML('afterbegin','<div class=&quot;image-placeholder&quot;>TECHTODAY</div>');"
                            >

                        <?php else: ?>

                            <div class="image-placeholder">
                                TECHTODAY
                            </div>

                        <?php endif; ?>

                        <span class="featured-badge">
                            FEATURED STORY
                        </span>

                    </a>


                    <div class="featured-content">

                        <div class="article-meta">

                            <span class="category-tag">
                                <?= htmlspecialchars(strtoupper(articleCategory($featuredArticle))) ?>
                            </span>

                            <span>
                                <?= htmlspecialchars(formatDate($featuredArticle['publishedAt'] ?? '')) ?>
                            </span>

                        </div>


                        <h3>
                            <?= htmlspecialchars($featuredArticle['title'] ?? 'Untitled article') ?>
                        </h3>


                        <p>
                            <?= htmlspecialchars(
                                $featuredArticle['description']
                                ?? 'Baca berita teknologi terbaru melalui TECHTODAY.'
                            ) ?>
                        </p>


                        <div class="article-footer">

                            <span>
                                <?= htmlspecialchars(
                                    $featuredArticle['source']['name']
                                    ?? 'News Source'
                                ) ?>
                            </span>


                            <a
                                href="<?= htmlspecialchars($featuredArticle['url'] ?? '#') ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Read Full Story <span>↗</span>
                            </a>

                        </div>

                    </div>

                </div>

            <?php endif; ?>


            <!-- ARTICLE GRID -->
            <div class="article-grid">

                <?php foreach ($otherArticles as $article): ?>

                    <article class="article-card">

                        <a
                            href="<?= htmlspecialchars($article['url'] ?? '#') ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="article-image"
                        >

                            <?php if (safeImage($article['urlToImage'] ?? '')): ?>

                                <img
                                    src="<?= htmlspecialchars($article['urlToImage']) ?>"
                                    alt="<?= htmlspecialchars($article['title'] ?? 'Technology news') ?>"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.remove();this.parentElement.insertAdjacentHTML('afterbegin','<div class=&quot;image-placeholder&quot;>TECHTODAY</div>');"
                                >

                            <?php else: ?>

                                <div class="image-placeholder">
                                    TECHTODAY
                                </div>

                            <?php endif; ?>


                            <span class="image-category">
                                <?= htmlspecialchars(strtoupper(articleCategory($article))) ?>
                            </span>

                        </a>


                        <div class="article-body">

                            <div class="article-date">
                                <?= htmlspecialchars(formatDate($article['publishedAt'] ?? '')) ?>
                            </div>


                            <h3>

                                <a
                                    href="<?= htmlspecialchars($article['url'] ?? '#') ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?= htmlspecialchars(
                                        $article['title']
                                        ?? 'Untitled article'
                                    ) ?>
                                </a>

                            </h3>


                            <p>
                                <?= htmlspecialchars(
                                    $article['description']
                                    ?? 'Baca informasi selengkapnya melalui sumber berita.'
                                ) ?>
                            </p>


                            <div class="article-card-footer">

                                <span>
                                    <?= htmlspecialchars(
                                        $article['source']['name']
                                        ?? 'News Source'
                                    ) ?>
                                </span>


                                <a
                                    href="<?= htmlspecialchars($article['url'] ?? '#') ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Read article"
                                >
                                    ↗
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>


        <?php endif; ?>

    </div>

</section>


<!-- ABOUT -->
<section class="about-section" id="about">

    <div class="container about-container">

        <div class="about-label">
            ABOUT TECHTODAY
        </div>


        <div class="about-content">

            <h2>
                Technology moves fast.<br>
                <span>Stay informed.</span>
            </h2>


            <p>
                TECHTODAY adalah portal informasi digital yang
                menghadirkan berita dan perkembangan terbaru
                seputar teknologi. Mulai dari kecerdasan buatan,
                perangkat digital, keamanan siber, hingga inovasi
                perusahaan teknologi.
            </p>


            <a href="#latest" class="about-link">
                Discover the latest stories <span>→</span>
            </a>

        </div>


        <div class="about-symbol">
            T.
        </div>

    </div>

</section>


<!-- FOOTER -->
<footer class="footer">

    <div class="container footer-container">

        <a href="index.php" class="brand footer-brand">

            <span class="brand-icon"><i></i></span>

            <span>
                TECH<span class="brand-highlight">TODAY</span>
            </span>

        </a>


        <p>
            Technology. Innovation. Digital Future.
        </p>


        <span class="copyright">
            © <?= date('Y') ?> TECHTODAY. Powered by NewsAPI.
        </span>

    </div>

</footer>


<!-- IMAGE FALLBACK STYLE -->
<style>
.image-placeholder {
    width: 100%;
    height: 100%;
    min-height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background:
        radial-gradient(
            circle at 75% 25%,
            rgba(44, 130, 255, .35),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            #07182f,
            #123d70
        );
    color: #ffffff;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 3px;
}

.featured-image .image-placeholder {
    min-height: 320px;
}

.article-image .image-placeholder {
    min-height: 180px;
}
</style>

</body>
</html>
