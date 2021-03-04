public function importCSVToMySql()
    {

        // for multiple files
        $files = [
            [
                'model' => 'client',
                'table' => 'clients',
                'file' => storage_path('app/exports/cdk/TINCX_CLI.csv'),
                'unique' => 'Codice Cliente'
            ],
            [
                'model' => 'article',
                'table' => 'articles',
                'file' => storage_path('app/exports/cdk/TINCX_MAG.csv'),
                'unique' => 'Codice articolo'
            ],
        ];


        foreach ($files as $file) {
            $row = 0;
            $allids = [];
            $columns = [];
            $uniqueKeyIndex = 0;
            $elequoteName = 'App\\Models\\' . ucfirst($file['model']);
            $dbTable = new $elequoteName();
            if (($handle = fopen($file['file'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
                    $row++;
                    if ($row === 1) {
                        foreach ($data as $index => $column) {
                            $columns [] = $column;
                            if ($column == $file['unique']) {
                                $uniqueKeyIndex = $index;
                            }
                        }
                    } else if ($row === 10) {
                        break;
                    } else {
                        if (!empty($columns)) {
                            $insertdb = $dbTable::updateOrCreate([$file['unique'] => $data[$uniqueKeyIndex]]);

                            foreach ($data as $index => $value) {
                                $columnName = $columns[$index];
                                if ($columns[$index] === $file['unique']) {
                                    $allids[] = $value;
                                }
                                if (Schema::hasColumn($file['table'], $columnName)) {
                                    $insertdb->$columnName = $value;
                                } else {
                                    $data[] = [$columnName => $value];
                                }
                            }
                        }
                        $insertdb->save();
                    }
                }
                if (!empty($allids)) {
                    $dbTable->whereNotIn($file['unique'], $allids)->delete();
                }
                fclose($handle);
            }

        }


        //print_r($data);


    }
