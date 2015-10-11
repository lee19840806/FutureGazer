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
 * @property \Cake\ORM\Association\HasMany $FileFields
 * @property \Cake\ORM\Association\HasOne $FileContents
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
    
    public function saveMaturation($fields = NULL, $curves = NULL, $dataType = NULL, $userID = NULL)
    {
        
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
            charged_off_amount float
        );
        
        insert into future_gazer.maturation_curves_raw
        values ("36 months", "A", 0, 1000, 0), ("36 months", "A", 5, 1000, 10), ("60 months", "B", 7, 2000, 300);
        
        select term.term, grade.grade, MoB.number as MoB, mc.origination_amount, mc.charged_off_amount,
            case when mc.charged_off_amount / mc.origination_amount is null then 0
                 else mc.charged_off_amount / mc.origination_amount end as charge_off_rate
        from future_gazer.numbers as MoB
            cross join future_gazer.unique_term as term
            cross join future_gazer.unique_grade as grade
            left join future_gazer.maturation_curves_raw as mc
            on MoB.number = mc.MoB and term.term = mc.term and grade.grade = mc.grade
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
        }
        
        $pipeline[2]['$group']['_id'][$MoBVariable] = '$Content.' . $MoBVariable;
        $pipeline[2]['$group'][$MoBVariable] = array('$first' => '$Content.' . $MoBVariable);
        $pipeline[2]['$group'][$originationVariable] = array('$sum' => '$Content.' . $originationVariable);
        $pipeline[2]['$group'][$chargeOffAmountVariable] = array('$sum' => '$Content.' . $chargeOffAmountVariable);
        
        $maturationRawData = $collectionFiles->aggregate($pipeline, array('allowDiskUse' => true));
        
        $maxMoB = $this->prepareMaxMoB($userID, $fileName, $MoBVariable);
        $maturationCurves = $this->prepareMaturationCurve($sqlIndex, $MoBVariable, $maxMoB, $originationVariable, $chargeOffAmountVariable, $maturationRawData['result']);
        
        $maturation = array();
        $maturation['segment'] = array_keys($sqlIndex);
        $maturation['mob'] = $MoBVariable;
        $maturation['curves'] = $maturationCurves;
    
        return $maturation;
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
    
    private function prepareMaturationCurve($segmentVariables = NULL, $MoBVariable = NULL, $maxMoB = NULL,
        $originationVariable = NULL, $chargeOffAmountVariable = NULL, $maturationCurvesRaw = NULL)
    {
        $connection = ConnectionManager::get('default');
        $databaseName = $connection->config()['database'];
        $select = 'SELECT ';
        $from = ' FROM ' . $databaseName . '.numbers as ' . $MoBVariable;
        $on = ' ON ' . $MoBVariable . '.number = mc.' . $MoBVariable;
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
            $from .= ' CROSS JOIN ' . $temporaryTableName . ' as ' . $key;
            $on .= ' and ' . $key . '.' . $key . ' = mc.' . $key;
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
        
        $select .= 'MoB.number as ' . $MoBVariable . ', mc.' . $originationVariable . ', mc.' . $chargeOffAmountVariable;
        $select .= ', case when mc.' . $chargeOffAmountVariable . ' / mc.' . $originationVariable . ' is null then 0 else '
            . 'mc.' . $chargeOffAmountVariable . ' / mc.' . $originationVariable . ' end as charge_off_rate';
        $from .= ' left join ' . $databaseName . '.maturation_curves_raw as mc';
        $order .= 'MoB.number';
        
        $results = $connection->execute($select . $from . $on . $where . $order)->fetchAll('assoc');
        return $results;
    }
    
    public function isOwnedBy($fileID = NULL, $userID = NULL)
    {
        return $this->exists(['id' => $fileID, 'user_id' => $userID]);
    }
}
