<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Storage;

class Categories extends Model
{
    use HasFactory;
    
    const PATH_FILE    = 'manteinance/categories/';
    const NAME_FILE    = 'category.json';
    const DOWNLOAD_URL = 'https://sdmx.istat.it/SDMXWS/rest/categoryscheme/IT1';
    
    // GENIALATA DI UN MODEL VIRTUALE ALIMENTATO DA UN JSON E NON DA UN DB!!!
    
    public static function getAll() {
        
        $array = json_decode(Storage::disk('local')->get(self::PATH_FILE . self::NAME_FILE));
        
        $category_schema = 'urn:sdmx:org.sdmx.infomodel.categoryscheme.CategoryScheme=IT1:ISTAT_DW(1.0)';
        
        $categories = self::_recursive_category($array->references->{$category_schema}->items);
        
        usort($categories, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });
        
        return $categories;
        
    }
    
    private static function _recursive_category($items) {
        $cats = [];
        foreach ($items as $item) {            
            $res['value']   = $item->id;
            $res['label'] = $item->name;
            if(isset($item->items)){
                $res['children'] = self::_recursive_category($item->items);
            }
            $cats[] = $res;
        }
        return $cats;
    }
    
}
