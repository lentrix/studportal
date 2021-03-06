<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'position', 'party', 'tag_line', 'short_bio'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public static function getByPosition($position, $dept=false) {
        $cnd = static::where('position', $position)
            ->join('users','candidates.user_id','users.id')
            ->select(['candidates.*','users.lname','users.fname','users.dept'])
            ->orderByRaw('users.lname, users.fname');

        if($dept) {
            $cnd->where('dept', $dept);
        }

        return $cnd->get();
    }

    public static function getList($position, $dept=false) {
        $data = static::getByPosition($position, $dept);

        $list = [];

        foreach($data as $candidate) {
            $list[$candidate->id] = $candidate->user->fullName . ' - [' . $candidate->party . ']';
        }

        return $list;
    }

    public static function count($position, $dept=null) {
        $cnds = static::getByPosition($position, $dept);

        $data = [];

        foreach($cnds as $cnd) {
            $count = Vote::where('candidate_id', $cnd->id)->count();
            $data[] = [
                'candidate' => $cnd->user->fullName,
                'count' => $count
            ];
        }

        $column = array_column($data, 'count');
        array_multisort($column, SORT_DESC, $data);

        return $data;
    }

}
