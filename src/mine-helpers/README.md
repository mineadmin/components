# MineAdmin Helpers Library

# FastBuilderWhere

```php
use Hyperf\Database\Model\Builder;

use Mine\Helper\FastBuilderWhere as BaseBuilder;

// old 
class oldDao {
    public function handleSearch(Builder $builder,array $params) {
        if (!empty($params['username'])){
            $builder->where('username',$params['username']);
        }
        if (!empty($params['user_created_at'])){
            list($startDate,$endDate) = $params['user_created_at'];
            $builder->whereBetween('created_at',[
            \Carbon\Carbon::createFromFormat('Y-m-d',$startDate)->startOfDay()->format('Y-m-d'),
            \Carbon\Carbon::createFromFormat('Y-m-d',$endDate)->startOfDay()->format('Y-m-d')
            ]);
        }
        if (!empty($params['sign_timestamp_at'])){
            list($startDate,$endDate) = $params['created_at'];
            $builder->whereBetween('created_at',[
            \Carbon\Carbon::createFromFormat('Y-m-d',$startDate)->startOfDay()->timestamp,
            \Carbon\Carbon::createFromFormat('Y-m-d',$endDate)->startOfDay()->timestamp
            ]);
        }
        return $builder;
    }
}

// new dao
class newDao {
    public function handleSearch(BaseBuilder $builder,array $params) {
        $builder->eq('username')
                ->dateRange('created_at','user_created_at')
                ->timestampsRange('created_at','sign_timestamp_at');
        return $builder;
    }
}

```