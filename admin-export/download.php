<?php
require_once '../../../../wp-load.php';
require_once './functions.php';
$errors = array();
if ( is_user_logged_in() ) {
    check_admin_referer( 'bookmark_exporter' );

    global $wpdb;
    $links_values  = esc_htmls( $_POST['links_values'] );
    $link_id_from  = esc_html( $_POST['link_id_from'] );
    $link_id_to    = esc_html( $_POST['link_id_to'] );
    $link_category = esc_html( $_POST['link_category'] );
    $limit         = esc_html( $_POST['limit'] );
    $offset        = esc_html( $_POST['offset'] );
    $order_by      = esc_html( $_POST['order_by'] );

    // SQL文作成
    $query = "";
    $value_parameter = array();

    //プレースホルダーに代入する値
    if ( !empty($links_values) ) {
        foreach ( $links_values as $key => $value ) {
            $query_select .= ', '.$value;
        }
    }
    $query .= "SELECT link_id".$query_select." ";

    // FROM
    $query .= " FROM ".$wpdb->links." ";

    // WHERE
    //範囲指定: link_id
    if ( !empty( $link_id_from ) && !empty( $link_id_to ) ) {
        $query .= "WHERE link_id BETWEEN %d AND %d ";
        $value_parameter[] = $link_id_from;
        $value_parameter[] = $link_id_to;
    }

    // ORDER
    if ( $order_by == 'DESC' ) {
        $query .= "ORDER BY link_id DESC ";
    }elseif ( $order_by == 'ASC' ) {
        $query .= "ORDER BY link_id ";
    }

    // LIMIT
    if ( !empty( $limit ) ) {
        $query .= "LIMIT %d ";
        $value_parameter[] = $limit;
    }

    //DBから取得
    $prepare = $wpdb->prepare( $query, $value_parameter );
    $results = $wpdb->get_results( $prepare, ARRAY_A );

    // カテゴリを追加
    $results = array_map( function ( $result ) {
        $customs_array = array();

        if ( !empty( $_POST['link_category'] ) ) {
            $link = get_bookmark( $result['link_id'], ARRAY_A );
            $link_cats = $link['link_category'];
            $customs_array += array( $_POST['link_category'] => implode(',', $link_cats) );
        }

        return array_merge( $result, $customs_array );
    }, $results );

    //結果があれば
    if ( !empty( $results ) ) {
        // 項目名を取得
        $head[] = array_keys( $results[0] );

        // 先頭に項目名を追加
        $list = array_merge( $head, $results );

        // ファイルの保存場所を設定
        $filename = 'export-link-'.date_i18n( "Y-m-d_H-i-s" ).'.csv';
        $filepath = dirname(__FILE__).'/temp/'.$filename;
        $fp = fopen( $filepath, 'w' );

        // 配列をカンマ区切りにしてファイルに書き込み
        foreach ( $list as $fields ) {
            fputcsv( $fp, $fields, ',', '"' );
        }
        fclose( $fp );

        //ダウンロードの指示
        header( 'Content-Type:application/octet-stream' );
        header( 'Content-Disposition:filename='.$filename );  //ダウンロードするファイル名
        header( 'Content-Length:' . filesize( $filepath ) );   //ファイルサイズを指定
        readfile( $filepath );  //ダウンロード
        unlink( $filepath );

    }else {
        //結果がない場合
        $errors[] = 'Links has not been exist.';
    }

}else{
    $errors[] = 'error!';
}

//エラー表示
if(!empty($errors)){
    foreach ($errors as $key => $value) {
        echo $value.PHP_EOL;
    }
    return;

}
