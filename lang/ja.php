<?php
return [
    // header
    'nav.top'   => 'トップ',
    'nav.about' => 'お店紹介',
    'nav.menu'  => 'メニュー',
    'nav.cart'  => 'カート',
    'nav.admin' => 'お店の管理',

    // menu page
    'menu.title'    => 'メニュー',
    'menu.closed'   => 'この日の予約は締め切りました',
    'menu.none'     => '条件に合うベーグルがありません。',
    'filter.day'    => 'OPEN日',
    'filter.cat'    => '種類',
    'filter.all'    => 'すべて',
    'filter.button' => 'しぼりこむ',
    'soldout'       => '売り切れ',

    // product page
    'product.back'              => '← メニューにもどる',
    'product.notfound'          => 'ベーグルが見つかりません',
    'product.allergens'         => 'アレルギー',
    'product.allergens_unknown' => '確認中です。くわしくはお店にお問い合わせください。',
    'product.days'              => '買える日',
    'product.no_days'           => '次のOPEN日の販売はまだ決まっていません。',
    'product.pickup_day'        => '受け取る日',
    'product.qty'               => '個数',
    'product.add'               => 'カートに入れる',

    // top page
    'home.title'    => 'ベーグルの予約',
    'home.next'     => '次のOPEN日',
    'home.left'     => '予約の締切まで あと %d日 %d時間',
    'home.see_menu' => 'メニューを見る',
    'home.no_next'  => '次のOPEN日はまだ決まっていません。',
    'home.picks'    => 'おすすめベーグル',

    // about page
    'about.intro'       => '福岡県中間市の小さなベーグル屋さんです。OPEN日は不定期で、ほぼ週1回です。',
    'about.address'     => '住所：',
    'about.hours'       => '営業時間：',
    'about.our_address' => '福岡県中間市鍋山町2-14',
    'about.our_hours'   => '11:00〜16:00（OPEN日のみ）',
    'about.shops'       => 'ベーグルが買えるお店',
    'about.unknown'     => '確認中',

    // order (complete page, cart, checkout)
    'order.day'      => '受け取り日',
    'order.time'     => '来店時間',
    'order.name'     => 'お名前',
    'order.bagel'    => 'ベーグル',
    'order.price'    => '値段',
    'order.subtotal' => '小計',
    'order.total'    => '合計 %d 個',

    // complete page
    'done.title' => 'ご予約ありがとうございました！',
    'done.code'  => '予約番号',
    'done.tell'  => 'お店で予約番号をお伝えください。',
    'done.back'  => 'トップへもどる',

    // cart page
    'cart.title'     => 'カート',
    'cart.added'     => 'カートに入れました。',
    'cart.other_day' => 'カートには、ちがう受け取り日の商品が入っています。先にカートを空にしてください。',
    'cart.empty'     => 'カートは空です。',
    'cart.until'     => '（ご予約は %s まで）',
    'cart.change'    => '変更',
    'cart.remove'    => '削除',
    'cart.min4'      => '4個以上からのご予約をお願いしています。',
    'cart.continue'  => '← 買い物を続ける',
    'cart.clear'     => 'カートを空にする',
    'cart.checkout'  => '予約へ進む',

    // checkout page
    'checkout.title'      => 'ご予約',
    'checkout.confirm'    => 'ご予約内容の確認',
    'checkout.required'   => '（必須）',
    'checkout.optional'   => '（なくてもOK）',
    'checkout.choose'     => 'えらんでください',
    'checkout.back_cart'  => '← カートにもどる',
    'checkout.next'       => '確認画面へ',
    'checkout.none'       => 'なし',
    'checkout.fix'        => '← 入力を直す',
    'checkout.place'      => '予約を確定する',
    'order.phone'         => '電話番号',
    'order.email'         => 'メールアドレス',
    'order.note'          => 'ご要望',
    'error.name'          => 'お名前を入れてください。',
    'error.name_long'     => 'お名前は100文字までです。',
    'error.phone'         => '電話番号は数字とハイフンで入れてください。（例：090-1234-5678）',
    'error.email'         => 'メールアドレスの形がちがいます。',
    'error.time'          => '来店時間を選んでください。',
    'error.note'          => 'ご要望は500文字までです。',

    // reservation rules
    'rule.no_day'   => '受け取り日が見つかりません。',
    'rule.closed'   => 'この日のご予約は締め切りました。',
    'rule.not_sold' => '販売していない商品が入っています。',
    'rule.not_day'  => '%sはこの日は販売していません。',
    'rule.soldout'  => '%sは売り切れです。',
    'rule.max'      => '%sは%d個までです。',
    'rule.left'     => 'この日はあと%d個までしか予約できません。',
];
