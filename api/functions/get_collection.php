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

    // $q = "select * from public.collection where key='". db_prep_sql($values) ."'";
    // $info = db_fetch($q,"Getting collection information");

    // $q = "
    //     select
    //         public.asset.id
    //         ,public.asset.title as asset
    //         ,collection_list_map.id as collection_list_map_id
    //         ,collection_list_map.connected
    //         ,collection_list_map.is_multi
    //         ,collection_list_map.list_id
    //         ,collection_list_map.collection_id
    //         ,collection_list_map.label
    //         ,collection_list_map.randomize
    //         ,collection_list_map.display_limit
    //         ,list_asset_map.filters
    //         ,list.title as list_title
    //         ,list.tables as tables
    //     from public.asset
    //     join public.list_asset_map on 
    //         list_asset_map.asset_id = asset.id
    //     join public.collection_list_map on
    //         collection_list_map.list_id = list_asset_map.list_id
    //         and collection_list_map.collection_id = '". $info['id'] ."'
    //     join public.list on
    //         list.id = collection_list_map.list_id
    //     order by
    //         collection_list_map.id
    //         ,collection_list_map.connected
    //         ,asset.id
    // ";

    // $assets_res = db_query($q,"Getting collection assets");


    $q = "
        select
            public.collection.*
            ,system.users.username
        from public.collection
        join system.users on
            system.users.id = public.collection.user_id
        where
            key='". db_prep_sql($values) ."'
    ";
    $info = db_fetch($q,"Getting collection information");

    $output = [
        "type" => "collection"
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

    // $q = "
    // select
    //     public.asset.*
    //     ,list_asset_map.filters
    // from public.asset
    // join public.list_asset_map on 
    //     list_asset_map.asset_id = asset.id
    //     and list_asset_map.list_id = '". $info['id'] ."'
    // order by
    //     asset.id
    // ";
    // $res = db_query($q,"Getting list assets");

    $q = "
        select
            public.asset.id
            ,public.asset.title as asset
            ,collection_list_map.id as collection_list_map_id
            ,collection_list_map.connected
            ,collection_list_map.is_multi
            ,collection_list_map.list_id
            ,collection_list_map.collection_id
            ,collection_list_map.label
            ,collection_list_map.randomize
            ,collection_list_map.display_limit
            ,list_asset_map.filters
            ,list.title as list_title
            ,list.key as list_key
            ,list.filter_labels
            ,list.tables as tables
        from public.asset
        join public.list_asset_map on 
            list_asset_map.asset_id = asset.id
        join public.collection_list_map on
            collection_list_map.list_id = list_asset_map.list_id
            and collection_list_map.collection_id = '". $info['id'] ."'
        join public.list on
            list.id = collection_list_map.list_id
        order by
            collection_list_map.id
            ,collection_list_map.connected
            ,asset.id
    ";
    $res = db_query($q,"Getting collection assets");

    $assets = [];
    $is_multi = [];
    while($row = db_fetch_row($res)) {
        if($row['is_multi']) {
            $is_multi[$row['connected']][$row['collection_list_map_id']] = $row['list_key'];
        }
        if(empty($assets[$row['list_key']]['list_title'])) {
            $assets[$row['list_key']] = [
                "list_title" => $row['list_title']
                ,"list_label" => $row['label']
                ,"randomize" => ($row['randomize'] == "t" ? 1 : 0)
                ,"display_limit" => $row['display_limit']
                ,"list_id" => $row['list_id']
                ,"tables" => ($row['tables'] == "t" ? 1 : 0)
                ,"connected" => $row['connected']
                ,"is_multi" => ($row['is_multi'] == "t" ? 1 : 0)
                ,'filter_count' => 0
                ,'assets' => []
                ,'filters' => json_decode($row['filter_labels'])
            ];
        }

        if(!empty($row['filters'])) {
            $assets[$row['list_key']]['filter_count'] += 1;
        }
        $assets[$row['list_key']]['assets'][] = [$row['asset'],json_decode($row['filters'])];
    }

    $output['lists'] = $assets;
    $output['is_multi'] = $is_multi;

    return json_encode($output);
}