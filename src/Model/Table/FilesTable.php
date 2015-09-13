<?php
namespace App\Model\Table;

use App\Model\Entity\File;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\ConnectionManager;

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
        // MongoDB aggregation example (MongoShell version)
        /*
        db.Files.aggregate([
        {$match: {UserID: 1, FileName: "lending_club.csv"}},
        {$unwind: "$Content"},
        {$group: {
            _id: {term: "$Content.term", grade: "$Content.grade", MoB: "$Content.MoB"},
            term: {$first: "$Content.term"},
            grade: {$first: "$Content.grade"},
            MoB: {$first: "$Content.MoB"},
            origination_amount: {$sum: "$Content.origination_amount"},
            charge_off_amount: {$sum: "$Content.charged_off_amount"}
        }},
        {$sort: {_id: 1}}
        ],
        {allowDiskUse: true}
        )
        */
        
        // MySQL temporary table example
        /*
        drop temporary table IF EXISTS future_gazer.unique_term;
        
        create temporary table future_gazer.unique_term
        (
            term varchar(255)
        );
        
        insert into future_gazer.unique_term
        values ("36 months"), ("60 months");
        
        drop temporary table IF EXISTS future_gazer.unique_grade;
        
        create temporary table future_gazer.unique_grade
        (
            grade varchar(255)
        );
        
        insert into future_gazer.unique_grade
        values ("A"), ("B"), ("C"), ("D"), ("E"), ("F"), ("G");
        
        drop temporary table IF EXISTS future_gazer.maturation_curves_raw;
        
        create temporary table future_gazer.maturation_curves_raw
        (
            term varchar(255),
            grade varchar(255),
            MoB int(11),
            origination_amount float,
            charge_off_amount float
        );
        
        insert into future_gazer.maturation_curves_raw
        values ("36 months", "A", 0, 1000, 0), ("36 months", "A", 5, 1000, 10), ("60 months", "B", 7, 2000, 300);
        
        select term.term, grade.grade, MoB.number as MoB, temp_maturation.origination_amount, temp_maturation.charge_off_amount
        from future_gazer.unique_term as term
            cross join future_gazer.unique_grade as grade
            cross join future_gazer.numbers as MoB
            left join future_gazer.maturation_curves_raw as temp_maturation
            on term.term = temp_maturation.term and grade.grade = temp_maturation.grade and MoB.number = temp_maturation.MoB
        where MoB.number <= 67
        order by term.term, grade.grade, MoB.number;
        */
        
        $mongoConnection = new \MongoClient();
        $collectionFiles = $mongoConnection->selectDB($this->mongoDatabase)->selectCollection($this->mongoCollection);
    
        $fileName = h($this->find()->select(['id', 'name'])->where(['id' => $options['fileID'], 'user_id' => $userID])->first()->name);
        $fields = $collectionFiles->findOne(array('UserID' => $userID, 'FileName' => $fileName), array('Fields' => 1));
        
        $originationVariable = $fields['Fields'][$options['origination']];
        $chargeOffAmountVariable = $fields['Fields'][$options['chargeOff']];
        $MoBVariable = $fields['Fields'][$options['MoB']];
        
        if ($options['segmentVariables'] == null)
        {
            $options['segmentVariables'] = array();
        }
        
        $pipeline = array(
            array('$match' => array('UserID' => $userID, 'FileName' => $fileName)),
            array('$unwind' => '$Content'),
            array('$group' => array('_id' => array())),
            array('$sort' => array('_id' => 1))
        );
        
        $pipelineOrigination = array(
            array('$match' => array('UserID' => $userID, 'FileName' => $fileName)),
            array('$unwind' => '$Content'),
            array('$group' => array('_id' => array())),
            array('$sort' => array('_id' => 1))
        );
        
        $pipelineChargeOff = array(
            array('$match' => array('UserID' => $userID, 'FileName' => $fileName)),
            array('$unwind' => '$Content'),
            array('$group' => array('_id' => array())),
            array('$sort' => array('_id' => 1))
        );
        
        $sqlIndex = array();
        
        foreach ($options['segmentVariables'] as $fieldIndex)
        {
            $field = $fields['Fields'][$fieldIndex];
            
            $sqlIndex[$field] = $this->preparePipelineUniqueField($userID, $fileName, $field);
            
            $pipeline[2]['$group']['_id'][$field] = '$Content.' . $field;
            $pipeline[2]['$group'][$field] = array('$first' => '$Content.' . $field);
            
//             $pipelineOrigination[2]['$group']['_id'][$field] = '$Content.' . $field;
//             $pipelineOrigination[2]['$group'][$field] = array('$first' => '$Content.' . $field);
//             $pipelineChargeOff[2]['$group']['_id'][$field] = '$Content.' . $field;
//             $pipelineChargeOff[2]['$group'][$field] = array('$first' => '$Content.' . $field);
        }
        
        $pipeline[2]['$group']['_id'][$MoBVariable] = '$Content.' . $MoBVariable;
        $pipeline[2]['$group'][$MoBVariable] = array('$first' => '$Content.' . $MoBVariable);
        $pipeline[2]['$group'][$originationVariable] = array('$sum' => '$Content.' . $originationVariable);
        $pipeline[2]['$group'][$chargeOffAmountVariable] = array('$sum' => '$Content.' . $chargeOffAmountVariable);
        
//         $pipelineOrigination[2]['$group']['_id'][$MoBVariable] = '$Content.' . $MoBVariable;
//         $pipelineOrigination[2]['$group'][$MoBVariable] = array('$first' => '$Content.' . $MoBVariable);
//         $pipelineOrigination[2]['$group'][$originationVariable] = array('$sum' => '$Content.' . $originationVariable);
        
//         $pipelineChargeOff[2]['$group']['_id'][$MoBVariable] = '$Content.' . $MoBVariable;
//         $pipelineChargeOff[2]['$group'][$MoBVariable] = array('$first' => '$Content.' . $MoBVariable);
//         $pipelineChargeOff[2]['$group'][$chargeOffAmountVariable] = array('$sum' => '$Content.' . $chargeOffAmountVariable);
        
        $maturationRawData = $collectionFiles->aggregate($pipeline, array('allowDiskUse' => true));
        
        $maxMoB = $this->prepareMaxMoB($userID, $fileName, $MoBVariable);
        $fullIndex = $this->prepareFullIndex($sqlIndex, $MoBVariable, $maxMoB, $originationVariable, $chargeOffAmountVariable, $maturationRawData['result']);
        
//         $origination = $collectionFiles->aggregate($pipelineOrigination, array('allowDiskUse' => true));
//         $charge_off_MoB = $collectionFiles->aggregate($pipelineChargeOff, array('allowDiskUse' => true));
        
        $maturationRawDataKeyValue = array();
        
        foreach ($maturationRawData['result'] as $row)
        {
            $key = '';
        
            foreach ($options['segmentVariables'] as $fieldIndex)
            {
                $key = $key . $row[$fields['Fields'][$fieldIndex]] . ':';
            }
        
            $key = $key . $row[$MoBVariable];
            $maturationRawDataKeyValue[$key] = $row[$originationVariable];
        }
        
//         $originationKeyValue = array();
        
//         foreach ($origination['result'] as $value)
//         {
//             $key = '';
            
//             foreach ($options['segmentVariables'] as $fieldIndex)
//             {
//                 $key = $key . $value[$fields['Fields'][$fieldIndex]] . ':';
//             }
            
//             $key = $key . $value[$MoBVariable];
//             $originationKeyValue[$key] = $value[$originationVariable];
//         }
        
        $maturationCurves = array();
        
        foreach ($charge_off_MoB['result'] as $value)
        {
            $row = array();
            $key = '';
            
            foreach ($options['segmentVariables'] as $fieldIndex)
            {
                $row[$fields['Fields'][$fieldIndex]] = $value[$fields['Fields'][$fieldIndex]];
                $key = $key . $value[$fields['Fields'][$fieldIndex]] . ':';
            }
            
            $key = $key . $value[$MoBVariable];
            $row[$MoBVariable] = $value[$MoBVariable];
            $row[$originationVariable] = $originationKeyValue[$key];
            $row[$chargeOffAmountVariable] = $value[$chargeOffAmountVariable];
            $row['charge_off_rate'] = $row[$chargeOffAmountVariable] / $row[$originationVariable];
            
            array_push($maturationCurves, $row);
        }
    
        $a = 1;
        
        $fields = $collectionFiles->findOne(array('UserID' => $userID, 'FileName' => $fileName), array('Fields' => 1));
        $fields = array_merge($fields, array('fileID' => $fileID));
    
        return $fields;
    }
    
    private function preparePipelineUniqueField($userID = NULL, $fileName = NULL, $field = NULL)
    {
        $pipeline = array(
            array('$match' => array('UserID' => $userID, 'FileName' => $fileName)),
            array('$unwind' => '$Content'),
            array('$group' => array('_id' => array($field => '$Content.' . $field), 'uniqueValue' => array('$first' => '$Content.' . $field))),
            array('$sort' => array('_id' => 1))
        );
        
        $mongoConnection = new \MongoClient();
        $collectionFiles = $mongoConnection->selectDB($this->mongoDatabase)->selectCollection($this->mongoCollection);
        $mongoResult = $collectionFiles->aggregate($pipeline, array('allowDiskUse' => true));
        
        $sqlInsertValues = '("';
        
        foreach ($mongoResult['result'] as $value)
        {
            $sqlInsertValues .= $value['uniqueValue'] . '"),("';
        }
        
        $sqlInsertValues = substr($sqlInsertValues, 0, strlen($sqlInsertValues) - 3);
        
        return $sqlInsertValues;
    }
    
    private function prepareMaxMoB($userID = NULL, $fileName = NULL, $field = NULL)
    {
        $pipeline = array(
            array('$match' => array('UserID' => $userID, 'FileName' => $fileName)),
            array('$unwind' => '$Content'),
            array('$group' => array('_id' => null, 'maxMoB' => array('$max' => '$Content.' . $field)))
        );
    
        $mongoConnection = new \MongoClient();
        $collectionFiles = $mongoConnection->selectDB($this->mongoDatabase)->selectCollection($this->mongoCollection);
        $mongoResult = $collectionFiles->aggregate($pipeline, array('allowDiskUse' => true));
        
        return $mongoResult['result'][0]['maxMoB'];
    }
    
    private function prepareFullIndex($segmentVariables = NULL, $MoBVariable = NULL, $maxMoB = NULL,
        $originationVariable = NULL, $chargeOffAmountVariable = NULL, $maturationCurvesRaw = NULL)
    {
        $connection = ConnectionManager::get('default');
        $databaseName = $connection->config()['database'];
        $select = 'SELECT ';
        $from = ' FROM ';
        $where = ' WHERE MoB.number <= ' . $maxMoB;
        $order = ' ORDER BY ';
        
        $maturationRawColumns = '';
        
        foreach ($segmentVariables as $key => $value)
        {
            $temporaryTableName = $databaseName . '.unique_' . $key;
            
            $connection->execute('drop temporary table IF EXISTS ' . $temporaryTableName);
            $connection->execute('create temporary table ' . $temporaryTableName . ' (' . $key . ' varchar(255))');
            $connection->execute('insert into ' . $temporaryTableName . ' values ' . $value);
            
            $select .= $key . '.' . $key . ', ';
            $from .= $temporaryTableName . ' as ' . $key . ', ';
            $order .= $key . '.' . $key . ', ';
            
            $maturationRawColumns .= $key . ' varchar(255), ';
        }
        
        $maturationRawColumns .= $MoBVariable . ' int(11), ' . $originationVariable . ' float, ' . $chargeOffAmountVariable . ' float';
        $connection->execute('drop temporary table IF EXISTS ' . $databaseName . '.maturation_curves_raw');
        $connection->execute('create temporary table ' . $databaseName . '.maturation_curves_raw' . ' (' . $maturationRawColumns . ')');
        
        foreach ($maturationCurvesRaw as $row)
        {
            $insert = 'insert into ' . $databaseName . '.maturation_curves_raw value (';
            
            foreach ($segmentVariables as $key => $value)
            {
                $insert .= '"' . $row[$key] . '", ';
            }
            
            $insert .= '' . $row[$MoBVariable] . ', ' . $row[$originationVariable] . ', ' . $row[$chargeOffAmountVariable] . ')';
            $connection->execute($insert);
        }
        
        // To be done: implement the cross join and left join SQL statement to get the full-indexed maturation curves
        
        $select .= 'MoB.number as ' . $MoBVariable;
        $from .= $databaseName . '.numbers as ' . $MoBVariable;
        $order .= 'MoB.number';
        
        $results = $connection->execute($select . $from . $where . $order)->fetchAll('assoc');
        return $results;
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
