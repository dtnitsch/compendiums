<?php

function get_collection($values) {

    // Check for cache
    if(!empty($GLOBALS["caching_options"]["enabled"]) && $GLOBALS["caching_options"]["enabled"]) {
        $path = $GLOBALS["caching_options"]['dir'];
        $dir = $path . substr($values,0,2) ."/";
        $file = $dir . $values;
        if(is_file($file)) {
            echo file_get_contents($file);
            return true;
        }
    }
    
    $output = build_get_collection_assets($values);

    // Save the cache
    if(!empty($file)) {
        if(!is_file($file)) {
            echo "WROTE CACHE FILE";
            echo "<p>";
            echo $dir;
            mkdir($dir,0777,true);
            chmod($dir, 0777);
            file_put_contents($file,$output);
        }
    }
    echo $output;
    return true;
}

function build_get_collection_assets($values) {

    $output = [];

    $q = "
        select
            public.list.*
            ,system.users.username
        from public.list
        join system.users on
            system.users.id = public.list.user_id
        where
            key='". db_prep_sql($values) ."'
    ";
    $info = db_fetch($q,"Getting list information");

    $output = [
        "type" => "list"
        ,"version" => $info['version']
        ,"key" => $info['key']
        ,"title" => $info['title']
        ,"alias" => $info['alias']
        ,"username" => $info['username']
        ,"description" => $info['description']
        ,"created" => $info['created']
        ,"modified" => $info['modified']
        ,"lists" => []
    ];

    $q = "
    select
        public.asset.*
        ,list_asset_map.filters
    from public.asset
    join public.list_asset_map on 
        list_asset_map.asset_id = asset.id
        and list_asset_map.list_id = '". $info['id'] ."'
    order by
        asset.id
    ";
    $res = db_query($q,"Getting list assets");

    $assets[$values] = [];
    while($row = db_fetch_row($res)) {
        if(empty($assets[$values]['list_title'])) {
            $assets[$values] = [
                'list_title' => $info['title']
                // ,'list_label' => $info['label']
                ,'randomize' => 1
                ,'display_limit' => 20
                ,'filter_count' => 0
                ,'filters' => json_decode($info['filter_labels'])
                ,'list_key' => $values
                ,'tables' => (strtolower($info['tables']) == 't' ? 1 : 0)
                ,'assets' => []
            ];
        }

        if(!empty($row['filters'])) {
            $assets[$values]['filter_count'] += 1;
        }
        $assets[$values]['assets'][] = [$row['title'],json_decode($row['filters'])];
    }

    $output['lists'] = $assets;

    return json_encode($output);
}