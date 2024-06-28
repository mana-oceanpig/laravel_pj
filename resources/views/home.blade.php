<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LuminaMind - AIカウンセリングで心の健康をサポート</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #50E3C2;
            --accent-color: #F5A623;
            --text-color: #333333;
            --background-color: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', sans-serif;
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
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 0;
        }

        nav ul {
            display: flex;
            justify-content: space-between;
            list-style-type: none;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        h1, h2, h3 {
            font-family: 'Roboto', sans-serif;
        }

        .hero {
            background-color: var(--secondary-color);
            color: white;
            text-align: center;
            padding: 4rem 0;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .cta-button {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .cta-button:hover {
            background-color: #e69100;
        }
        .about {
            padding: 4rem 0;
        }

        .about {
            margin-bottom: 2rem;
        }

        .about h2 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        

        .features {
            padding: 4rem 0;
        }

        .feature {
            margin-bottom: 2rem;
        }

        .feature h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .faq {
            background-color: #f5f5f5;
            padding: 4rem 0;
        }

        .faq h2 {
            text-align: center;
            margin-bottom: 2rem;
        }

        .faq-item {
            margin-bottom: 1.5rem;
        }

        .faq-question {
            font-weight: bold;
            cursor: pointer;
            padding: 0.5rem 0;
        }

        .faq-answer {
            display: none;
            padding: 0.5rem 0;
        }

        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <ul>
                <li><a href="#home">ホーム</a></li>
                <li><a href="#about">LuminaMindとは</a></li>
                <li><a href="#features">機能</a></li>
                <li><a href="#faq">よくある質問</a></li>
                <li><a href="#contact">お問い合わせ</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="container">
                <h1>LuminaMind</h1>
                <p>AIカウンセリングで心の健康をサポート</p>
                <a href="conversation" class="cta-button">今すぐ始める</a>
            </div>
        </section>
        <section id="about" class="about">
            <div clas="container">
                <h2>デジタル世界に、心のオアシスを作る</h2>
                <div class="about">
                <p>日本で精神疾患を有する総患者数は年々増加しており、コロナ禍に増加・社会課題にもなりました。また、厚生労働省の調査によると、自分の仕事や職業生活に関することで強い不安・悩み又はストレスがあるとする労働者の割合は令和4年に82%にも上り、診断されていない「未病うつ」「隠れうつ」といった健康に不安を抱える潜在層がいることが推測されます。
                企業にとっても、中途社員のバーンアウトや適応障害や大きな損失です。そうなる前に、予防したい。それが私の想いです。
                しかし、精神疾患は職場や家族など、周囲の人物に知られたくないという気持ちがあり、発見が遅れてしまいがちです。
                LuminaMindは、AIのカウンセラーがいつでもどこでもクライアントをサポート。
                病院やカウンセラーに相談する前の選択肢、プレ診断、という選択肢を日本に作ります。
                </p>
                </div>
            </div>
        </section>

        <section id="features" class="features">
            <div class="container">
                <h2>LuminaMindの特徴</h2>
                <div class="feature">
                    <h3>AIカウンセラーとの対話</h3>
                    <p>最新のAI技術を活用し、あなたの心の状態を理解し、適切なアドバイスを提供します。</p>
                </div>
                <div class="feature">
                    <h3>神経伝達物質の可視化</h3>
                    <p>セロトニン、ドーパミン、オキシトシンの量を簡単な質問から推定し、わかりやすく表示します。</p>
                </div>
                <div class="feature">
                    <h3>カスタマイズされた回復プラン</h3>
                    <p>あなたの状態に合わせた具体的な回復方法を提案し、心の健康をサポートします。</p>
                </div>
            </div>
        </section>

        <section id="faq" class="faq">
            <div class="container">
                <h2>よくある質問</h2>
                <div class="faq-item">
                    <div class="faq-question">Q: LuminaMindは医療サービスですか？</div>
                    <div class="faq-answer">A: LuminaMindは医療サービスではありません。心の健康をサポートするツールとしてご利用ください。深刻な症状がある場合は、必ず医療専門家にご相談ください。</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Q: 個人情報は安全ですか？</div>
                    <div class="faq-answer">A: はい、LuminaMindは最新のセキュリティ技術を採用し、お客様の個人情報を厳重に管理しています。詳細は、プライバシーポリシーをご確認ください。</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Q: 利用料金はかかりますか？</div>
                    <div class="faq-answer">A: 基本機能は無料でご利用いただけます。より詳細な分析や高度な機能については、有料プランをご用意しています。</div>
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
        document.addEventListener('DOMContentLoaded', () => {
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', () => {
                    const answer = question.nextElementSibling;
                    answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
                });
            });
        });
    </script>
</body>
</html>