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
 */
class FilesTable extends Table
{
    private $mongoDatabase = 'DataFiles';
    private $mongoCollection = 'Files';

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
    
    public function saveFile($filePath = NULL, $fileName = NULL, $dataType = NULL, $userID = NULL)
    {
        $mongoConnection = new \MongoClient();
        $collectionFiles = $mongoConnection->selectDB($this->mongoDatabase)->selectCollection($this->mongoCollection);
    
        $document = array('UserID' => $userID, 'FileName' => $fileName, 'Fields' => array(), 'Content' => array());
        $handle = fopen($filePath, 'r');
    
        if ($handle !== FALSE)
        {
            $rowNumber = 0;
            $header = [];
    
            while (($rowData = fgetcsv($handle, 0, ',')) !== FALSE)
            {
                if ($rowNumber == 0)
                {
                    $header = $rowData;
                    
                    foreach ($rowData as $key => $cell)
                    {
                        array_push($document['Fields'], $cell);
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
                    
                    $row = array_merge(array('RowNum' => $rowNumber), array_combine($header, $rowData));
                    array_push($document['Content'], $row);
                }
                
                $rowNumber++;
            }
            
            fclose($handle);
            unlink($filePath);
    
            $collectionFiles->insert($document);
    
            $file = $this->newEntity();
            $file->user_id = $userID;
            $file->name = h($fileName);
            $this->save($file);
        }
    }
    
    public function viewFileContent($fileID = NULL, $userID = NULL)
    {
        $mongoConnection = new \MongoClient();
        $collectionFiles = $mongoConnection->selectDB($this->mongoDatabase)->selectCollection($this->mongoCollection);
    
        $fileName = h($this->find()->select(['id', 'name'])->where(['id' => $fileID, 'user_id' => $userID])->first()->name);
    
        $file = $collectionFiles->findOne(array('UserID' => $userID, 'FileName' => $fileName), array('Content.RowNum' => 0));
        $file = array_merge($file, array('fileID' => $fileID));
        
        return $file;
    }
    
    public function getFields($fileID = NULL, $userID = NULL)
    {
        $mongoConnection = new \MongoClient();
        $collectionFiles = $mongoConnection->selectDB($this->mongoDatabase)->selectCollection($this->mongoCollection);
    
        $fileName = h($this->find()->select(['id', 'name'])->where(['id' => $fileID, 'user_id' => $userID])->first()->name);
    
        $fields = $collectionFiles->findOne(array('UserID' => $userID, 'FileName' => $fileName), array('Fields' => 1));
        $fields = array_merge($fields, array('fileID' => $fileID));
    
        return $fields;
    }
    
    public function buildCurves($options = NULL, $userID = NULL)
    {
        $mongoConnection = new \MongoClient();
        $collectionFiles = $mongoConnection->selectDB($this->mongoDatabase)->selectCollection($this->mongoCollection);
    
        $fileName = h($this->find()->select(['id', 'name'])->where(['id' => $options['fileID'], 'user_id' => $userID])->first()->name);
        
        $pipelineOrigination = array(
            array('$match' => array('UserID' => $userID, 'FileName' => $fileName)),
            array('$unwind' => '$Content'),
            array('$group' => array(
                '_id' => array('term' => '$Content.term', 'grade' => '$Content.grade'),
                'origination_amount' => array('$sum' => '$Content.origination_amount'))),
            array('$sort' => array('_id' => 1))
        );
        
        $pipelineChargeOff = array(
            array('$match' => array('UserID' => $userID, 'FileName' => $fileName)),
            array('$unwind' => '$Content'),
            array('$group' => array(
                '_id' => array('term' => '$Content.term', 'grade' => '$Content.grade', 'MoB' => '$Content.MoB'),
                'charge_off_amount' => array('$sum' => '$Content.charged_off_amount'))),
            array('$sort' => array('_id' => 1))
        );
        
        $origination = $collectionFiles->aggregate($pipelineOrigination);
        $charge_off_MoB = $collectionFiles->aggregate($pipelineChargeOff);
    
        $a = 1;
        
        $fields = $collectionFiles->findOne(array('UserID' => $userID, 'FileName' => $fileName), array('Fields' => 1));
        $fields = array_merge($fields, array('fileID' => $fileID));
    
        return $fields;
    }
    
    public function isOwnedBy($fileID = NULL, $userID = NULL)
    {
        return $this->exists(['id' => $fileID, 'user_id' => $userID]);
    }
    
    public function deleteFile($fileID = NULL, $userID = NULL)
    {
        $mongoConnection = new \MongoClient();
        $collectionFiles = $mongoConnection->selectDB($this->mongoDatabase)->selectCollection($this->mongoCollection);
    
        $file = $this->get($fileID);
        $fileName = $file->name;
    
        if ($this->delete($file))
        {
            if ($collectionFiles->remove(array('UserID' => $userID, 'FileName' => $fileName), array("justOne" => true)))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}
