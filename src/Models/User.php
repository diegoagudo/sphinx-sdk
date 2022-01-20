<?php

namespace Plataforma13\SphinxSdk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Plataforma13\SphinxSdk\Contracts\UserModelContract;

/**
 * Class AbstractModel
 *
 * @package App\Models
 *
 * Autocomplete the Builder methods (for example where(), get(), find(), findOrFail() etc...)
 * @mixin Builder
 */
class User extends Model implements UserModelContract
{
    protected $table = 'users';
    protected $document = 'document';
    protected $fillable = [
        'document',
        'email',
    ];

    /**
     * @param string $document
     */
    public function setDocument(string $document) {
        $this->document = $document;
    }

    /**
     * @return string
     */
    public function getDocument() {
        return $this->document;
    }

    /**
     * @param array $data
     *
     * @return Model|object|User|null
     */
    public function findUser(array $data) {
        return $this->where($this->document, $data['document'])
            ->first();
    }
}
