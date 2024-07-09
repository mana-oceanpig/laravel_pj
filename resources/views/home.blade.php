<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LuminaMind - AIカウンセリングで心の健康をサポート</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #50E3C2;
            --accent-color: #F5A623;
            --text-color: #333333;
            --background-color: #FFFFFF;
            --light-gray: #F8F8F8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        header.scrolled {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        nav ul {
            display: flex;
            list-style-type: none;
        }

        nav ul li {
            margin-left: 2rem;
        }

        nav ul li a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: var(--primary-color);
        }

        h1, h2, h3 {
            font-family: 'Poppins', 'Noto Sans JP', sans-serif;
        }

        .hero {
            background-color: var(--light-gray);
            padding: 8rem 0 4rem;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hero-text {
            flex: 1;
            padding-right: 2rem;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--text-color);
        }

        .hero-image {
            flex: 1;
            position: relative;
        }

        .hero-image img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .cta-button {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .cta-button:hover {
            background-color: #e69100;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .section {
            padding: 6rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 3rem;
            text-align: center;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature {
            background-color: var(--light-gray);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .feature:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .feature h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .how-it-works-steps {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 2rem;
            background-color: var(--light-gray);
            border-radius: 10px;
            margin: 0 1rem;
            transition: all 0.3s ease;
        }

        .step:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            display: inline-block;
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            line-height: 50px;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .faq {
            background-color: var(--light-gray);
        }

        .faq-item {
            margin-bottom: 1.5rem;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .faq-question {
            font-weight: 600;
            cursor: pointer;
            padding: 1.5rem;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question::after {
            content: '+';
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .faq-question.active::after {
            transform: rotate(45deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-answer-content {
            padding: 1.5rem;
        }

        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 3rem 0;
        }

        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
            }

            .hero-text, .hero-image {
                flex: none;
                width: 100%;
                padding-right: 0;
                margin-bottom: 2rem;
            }

            .how-it-works-steps {
                flex-direction: column;
            }

            .step {
                margin: 1rem 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">LuminaMind</div>
            <ul>
                <li><a href="#home">ホーム</a></li>
                <li><a href="#about">LuminaMindとは</a></li>
                <li><a href="#features">機能</a></li>
                <li><a href="#how-it-works">利用方法</a></li>
                <li><a href="#faq">よくある質問</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="container hero-content">
                <div class="hero-text">
                    <h1>心のオアシス、<br>LuminaMind</h1>
                    <p>AIカウンセリングで、あなたの心の健康をサポートします。<br>24時間365日、いつでもどこでも。</p>
                    <a href="/conversations" class="cta-button">無料で始める</a>
                </div>
                <div class="hero-image">
                    <img src="/api/placeholder/600/400" alt="LuminaMind デモ画面">
                </div>
            </div>
        </section>

        <section id="about" class="section">
            <div class="container">
                <h2 class="section-title">LuminaMindとは</h2>
                <p>LuminaMindは、AIを活用した心の健康サポートサービスです。日本では年々増加する精神疾患患者数や、コロナ禍による心の健康への影響が社会課題となっています。また、多くの労働者が仕事や職業生活に関する強い不安やストレスを抱えています。</p>
                <p>私たちは、誰もが気軽に心の健康ケアにアクセスできる環境を作ることで、これらの課題に取り組みます。LuminaMindは、プライバシーを守りながら、いつでもどこでもサポートを提供し、心の健康を維持・改善するための新しい選択肢を提供します。</p>
            </div>
        </section>

        <section id="features" class="section">
            <div class="container">
                <h2 class="section-title">LuminaMindの特徴</h2>
                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">💬</div>
                        <h3>AIカウンセラーとの対話</h3>
                        <p>最新のAI技術を活用し、あなたの心の状態を理解し、適切なアドバイスを提供します。</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">🧠</div>
                        <h3>神経伝達物質の可視化</h3>
                        <p>セロトニン、ドーパミン、オキシトシンの量を簡単な質問から推定し、わかりやすく表示します。</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">📊</div>
                        <h3>カスタマイズされた回復プラン</h3>
                        <p>あなたの状態に合わせた具体的な回復方法を提案し、心の健康をサポートします。</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">🔒</div>
                        <h3>プライバシーの保護</h3>
                        <p>匿名で利用可能で、あなたの個人情報は厳重に保護されます。安心して利用いただけます。</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="section">
            <div class="container">
                <h2 class="section-title">LuminaMindの使い方</h2>
                <div class="how-it-works-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>アカウント作成</h3>
                        <p>簡単な手順でアカウントを作成します。個人情報は最小限で構いません。</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>初期アセスメント</h3>
                        <p>あなたの現在の状態を把握するための簡単な質問に答えます。</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>AIカウンセリング</h3>
                        <p>AIカウンセラーとチャットを通じて対話します。悩みや不安を相談しましょう。</p>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <h3>分析とアドバイス</h3>
                        <p>AIが分析結果とカスタマイズされたアドバイスを提供します。</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="section faq">
            <div class="container">
                <h2 class="section-title">よくある質問</h2>
                <div class="faq-item">
                    <div class="faq-question">LuminaMindは医療サービスですか？</div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            LuminaMindは医療サービスではありません。心の健康をサポートするツールですが、専門的な医療アドバイスや診断、治療の代替にはなりません。深刻な症状がある場合は、必ず医療専門家にご相談ください。
                        </div>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">個人情報は安全ですか？</div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            はい、LuminaMindはユーザーのプライバシーを最優先に考えています。すべての個人情報は暗号化され、厳重に保護されています。また、匿名での利用も可能です。
                        </div>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">LuminaMindの利用料金はいくらですか？</div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            LuminaMindには無料プランと有料プランがあります。基本的な機能は無料でご利用いただけます。より高度な機能や頻繁な利用をご希望の方には、月額制の有料プランをご用意しています。詳細は料金ページをご覧ください。
                        </div>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">LuminaMindはどのくらいの頻度で利用できますか？</div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            LuminaMindは24時間365日いつでもご利用いただけます。ただし、心身の健康のために、適度な利用をおすすめします。毎日の短時間の利用が最も効果的です。
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 LuminaMind. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // ヘッダーのスクロール処理
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // FAQの開閉処理
        const faqQuestions = document.querySelectorAll('.faq-question');
        faqQuestions.forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const isOpen = question.classList.contains('active');

                // 他の全ての回答を閉じる
                faqQuestions.forEach(q => {
                    if (q !== question) {
                        q.classList.remove('active');
                        q.nextElementSibling.style.maxHeight = null;
                    }
                });

                // クリックされた質問の回答を開閉する
                if (!isOpen) {
                    question.classList.add('active');
                    answer.style.maxHeight = answer.scrollHeight + "px";
                } else {
                    question.classList.remove('active');
                    answer.style.maxHeight = null;
                }
            });
        });
    </script>
</body>
</html>