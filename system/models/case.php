<?php
  class CaseModel extends BaseModel {
    function __construct( $dbConfig = null )
    {
      parent::__construct( $dbConfig );

      $this->queryTemplates = array_merge( $this->queryTemplates, [
        'getPrimaryAttorney' => 'SELECT u.* 
                                 FROM system_addressbook as u
                                      INNER JOIN cases AS c
                                        ON c.case_attorney = u.pkaddressbookid
                                 WHERE c.id = :case_id',

        'removeClient'       => 'DELETE 
                                 FROM sides_clients
                                 WHERE sides_clients.side_id IN ( 
                                   SELECT id FROM sides WHERE sides.case_id = :case_id
                                 ) AND sides_clients.client_id = :client_id',
        'removeUser' => 'DELETE 
                         FROM sides_users
                         WHERE sides_users.side_id IN ( 
                           SELECT id FROM sides WHERE sides.case_id = :case_id
                         ) AND sides_users.system_addressbook_id = :user_id',

        'getByUser' => 'SELECT c.*
                        FROM cases AS c
                          INNER JOIN sides AS s
                            ON c.id = s.case_id
                          LEFT JOIN sides_users AS su
                            ON su.side_id = s.id
                        WHERE su.system_addressbook_id = :user_id
                              OR s.primary_attorney_id = :user_id
                        ORDER BY c.case_title ASC',
        
        'getAllClients' => 'SELECT c.*
                            FROM clients AS c
                              INNER JOIN sides_clients AS sc
                                ON c.id = sc.client_id
                              INNER JOIN sides AS s
                                ON s.id = sc.side_id
                            WHERE s.case_id = :case_id',
      ]);
      $this->sides = new Side();
      $this->users = new User();
    }

    function getPrimaryAttorney($caseId) {
      $query = $this->queryTemplates['getPrimaryAttorney'];
      return User::publishable(
        $this->readQuery($query, ['case_id' => $caseId], 1)
      );
    }

    function find($caseId) {
      return $this->getBy('cases', ['id' => $caseId], 1);
    }

    function setSideMasterHead($side, $masterHead) {
      $this->sides->updateSide($side['id'], ['masterhead' => $masterHead]);
    }

    function setSideAttorney($side, $attorneyId, $updateTeam = true) {
      global $AdminDAO, $currentUser;

      $attorney = $this->users->find($attorneyId);
      
      if ($updateTeam) {
        $this->sides->addAttorneyTeam($side['id'], $attorneyId);
      }
      $this->sides->updateSide($side['id'], [
        'primary_attorney_id' => $attorneyId,
        'masterhead'          => $attorney['masterhead']
      ]);
      
      // add attorney to service list (BACKWARD COMP)
      $caseId = $side['case_id'];
      $slAttorney = $this->getBy('attorney', ['case_id' => $caseId, 'attorney_email' => $attorney['email']], 1);
      if (!$slAttorney) {
        $slAttorneyId = $this->insert('attorney', [
          'uid'             => $AdminDAO->generateuid('attorney'),
          'case_id'         => $caseId,
          'attorney_name'   => User::getFullName($attorney),
          'attorney_email'  => $attorney['email'],
          'fkaddressbookid' => $attorneyId,
          'attorney_type'   => 2,
          'updated_at'      => date('Y-m-d H:i:s'),
          'updated_by'      => $currentUser->id
        ], true);
        if ( (int)$slAttorneyId ) {
          $slAttorney = $this->getBy('attorney', ['id' => $slAttorneyId], 1);
        }        
      }
      if ($slAttorney) {
        $clients = $this->sides->getClients($side['id']);
        foreach($clients as $client) {
          $exists = $this->existsBy('client_attorney', [
            'case_id'     => $caseId,
            'attorney_id' => $slAttorney['id'],
            'client_id'   => $client['id'],
          ]);
          if (!$exists) {
            $this->insert('client_attorney', [
              'case_id'     => $caseId,
              'attorney_id' => $slAttorney['id'],
              'client_id'   => $client['id'],
              'updated_at'  => date('Y-m-d H:i:s'),
              'updated_by'  => $currentUser->id
            ], true);
          }
        }
      }
    }

    function updateCase($caseId, $fieldsMap, $updateTeam = false) {
      global $currentUser;

      $userSide = $this->sides->getByUserAndCase($currentUser->id, $caseId);
      $userSide = $userSide ? $userSide : $this->sides->create('', $caseId, null);
      if ($userSide) {
        $attorneyChanged = $fieldsMap['case_attorney']
                           && $userSide['primary_attorney_id'] != $fieldsMap['case_attorney'];
        $masterHeadChanged = $fieldsMap['masterhead']
                             && $userSide['masterhead'] != $fieldsMap['masterhead'];
        $this->update('cases', $fieldsMap, ['id' => $caseId]);
        if ($attorneyChanged) {
          $this->setSideAttorney($userSide, $fieldsMap['case_attorney'], $updateTeam);
        }
        else if ($masterHeadChanged) {
          $this->setSideMasterHead($userSide, $fieldsMap['masterhead']);
        }
      }
    }

    function removeClient($caseId, $clientId, $cleanupSides = false) {
      $query = $this->queryTemplates['removeClient'];
      $this->writeQuery($query, [
        'case_id' => $caseId,
        'client_id' => $clientId
      ]);
      if ($cleanupSides) {
        $this->sides->cleanupCase($caseId);
      }      
    }

    function getClients($caseId) {
      global $currentUser;

      $sides = new Side();
      $userSide = $sides->getByUserAndCase($currentUser->id, $caseId);
      $clients = $sides->getClients($userSide['id']);
      
      // BACKWARD COMP
      // $clientIds = BaseModel::pluck($clients, 'id');
      // $oldClients = $this->getBy('clients', ['case_id' => $caseId]);
      // collect non-repeating old clients
      // foreach($oldClients as $oldClient) {
      //   if ( !in_array($oldClient['id'], $clientIds ) ) {
      //     $clients[] = $oldClient;
      //   }
      // }

      return $clients;
    }

    function getUsers($caseId) {
      global $currentUser;

      $sides = new Side();
      $userSide = $sides->getByUserAndCase($currentUser->id, $caseId);
      $users = $sides->getUsers($userSide['id']);

      return $users;
    }

    function getByUser($userId) {
      $query = $this->queryTemplates['getByUser'];
      return $this->readQuery($query, ['user_id' => $userId]);
    }

    function getAllClients($caseId) {
      $query = $this->queryTemplates['getAllClients'];
      return $this->readQuery($query, ['case_id' => $caseId]);
    }

    function removeUser($caseId, $userId, $cleanupSides = false) {
      $query = $this->queryTemplates['removeUser'];
      $this->writeQuery($query, [
        'case_id' => $caseId,
        'user_id' => $userId
      ]);
      if ($cleanupSides) {
        $this->sides->cleanupCase($caseId);
      }
    }

  }

  $casesModel = new CaseModel();