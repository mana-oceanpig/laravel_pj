<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $features = [
            ['title' => 'AIカウンセラーとの対話', 'description' => '最新のAI技術を活用し、あなたの心の状態を理解し、適切なアドバイスを提供します。'],
            ['title' => '神経伝達物質の可視化', 'description' => 'セロトニン、ドーパミン、オキシトシンの量を簡単な質問から推定し、わかりやすく表示します。'],
            ['title' => 'カスタマイズされた回復プラン', 'description' => 'あなたの状態に合わせた具体的な回復方法を提案し、心の健康をサポートします。'],
        ];

        $faqs = [
            ['question' => 'LuminaMindは医療サービスですか？', 'answer' => 'LuminaMindは医療サービスではありません。心の健康をサポートするツールとしてご利用ください。深刻な症状がある場合は、必ず医療専門家にご相談ください。'],
            ['question' => '個人情報は安全ですか？', 'answer' => 'はい、LuminaMindは最新のセキュリティ技術を採用し、お客様の個人情報を厳重に管理しています。詳細は、プライバシーポリシーをご確認ください。'],
            ['question' => '利用料金はかかりますか？', 'answer' => '基本機能は無料でご利用いただけます。より詳細な分析や高度な機能については、有料プランをご用意しています。'],
        ];

        return view('home', compact('features', 'faqs'));
    }
}