<?php
namespace App\Model\Table;

use App\Model\Entity\File;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Files Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\HasMany $FileFields
 * @property \Cake\ORM\Association\HasOne $FileContents
 */
class FilesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('files');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        
        $this->hasMany('FileFields', [
            'foreignKey' => 'file_id',
            'sort' => ['FileFields.indx' => 'ASC'],
            'dependent' => true
        ]);
        
        $this->hasOne('FileContents', [
            'className' => 'FileContents',
            'foreignKey' => 'file_id',
            'dependent' => true
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create')
            ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->allowEmpty('description');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        return $rules;
    }
    
    public function saveFileToMySQL($filePath = NULL, $fileName = NULL, $dataType = NULL, $csvMeta = NULL, $userID = NULL)
    {
        $handle = fopen($filePath, 'r');
    
        if ($handle !== FALSE)
        {
            $fields = [];
            $content = [];
            $rowNumber = 0;
            
            while (($rowData = fgetcsv($handle, 0, $csvMeta['delimiter'])) !== FALSE)
            {
                if ($rowNumber == 0)
                {
                    foreach ($rowData as $key => $cell)
                    {
                        array_push($fields, $this->FileFields->newEntity(array('indx' => $key + 1, 'name' => h($cell), 'type' => $dataType[h($cell)])));
                    }
                }
                else
                {
                    foreach ($rowData as $key => $cell)
                    {
                        if (array_values($dataType)[$key] == 'number')
                        {
                            $rowData[$key] = floatval($rowData[$key]);
                        }
                        else
                        {
                            $rowData[$key] = h($rowData[$key]);
                        }
                    }
                    
                    array_push($content, array_combine(array_keys($dataType), $rowData));
                }
                
                $rowNumber++;
            }
            
            fclose($handle);
            unlink($filePath);
            
            $file = $this->newEntity();
            $file->user_id = $userID;
            $file->name = h($fileName);
            $file->file_fields = $fields;
            $file->file_content = $this->FileContents->newEntity(array('content' => json_encode($content)));
            
            if ($this->save($file))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    
    public function isOwnedBy($fileID = NULL, $userID = NULL)
    {
        return $this->exists(['id' => $fileID, 'user_id' => $userID]);
    }
}
