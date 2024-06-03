<?php

namespace App\Traits\Tests;

trait QuizQATrait
{
    private $singleQ = '{
        "id": 1,
        "type": "mcqSingle",
        "options": [
            {
                "id": 1,
                "value": "5:00"
            },
            {
                "id": 2,
                "value": "5:00"
            },
            {
                "id": 3,
                "value": "5:00"
            },
            {
                "id": 4,
                "value": "5:00"
            }
        ],
        "question": "What time is it? (hint its 5:00)"
    }';

    private $multipleQ = '{
        "id": 2,
        "type": "mcqMultiple",
        "options": [
            {
                "id": 1,
                "value": "5:00"
            },
            {
                "id": 2,
                "value": "8:00"
            },
            {
                "id": 3,
                "value": "10:00"
            },
            {
                "id": 4,
                "value": "12:00"
            }
        ],
        "question": "What time is it? (hint its 5:00, 8:00, 10:00 - truly a time paradox!)",
        "maxChoices": 3
    }';

    private $missingQ = '{
        "id": 3,
        "type": "missingWord",
        "options": [
            {
                "id": 1,
                "value": "5:00"
            },
            {
                "id": 2,
                "value": "8:00"
            },
            {
                "id": 3,
                "value": "10:00"
            },
            {
                "id": 4,
                "value": "12:00"
            }
        ],
        "question": "Time now is ___? (hint its 8:00)"
    }';

    private $linkingQ = '{
        "id": 4,
        "type": "linking",
        "options": {
            "leftSide": [
                [
                    {
                        "id": 1,
                        "value": "truck"
                    },
                    {
                        "id": 2,
                        "value": "computer"
                    },
                    {
                        "id": 3,
                        "value": "pizza"
                    },
                    {
                        "id": 4,
                        "value": "football"
                    }
                ]
            ],
            "rightSide": [
                [
                    {
                        "id": 5,
                        "value": "food"
                    },
                    {
                        "id": 6,
                        "value": "vehicle"
                    },
                    {
                        "id": 7,
                        "value": "sport"
                    },
                    {
                        "id": 8,
                        "value": "technology"
                    }
                ]
            ]
        },
        "question": "Link the proper relation"
    }';

    ////////////////////
    private $singleAt = '{
        "type": "mcqSingle",
        "answerId": "1"
    }';

    private $multipleAt = '{
        "type": "mcqMultiple",
        "maxChoices": "3",
        "answerId": [
            "1",
            "2",
            "3"
        ]
    }';

    private $missingAt = '{
        "type": "missingWord",
        "answerId": "3"
    }';

    private $linkingAt = '{
        "type": "linking",
        "answerId": {
            "1": "6",
            "2": "8",
            "3": "5",
            "4": "7"
        }
    }';
    ////////////////////

    private $singleA = '{
        "answerId": "1"
    }';

    private $multipleA = '{
        "answerId": {
            "1": true,
            "2": true,
            "3": true
        }
    }';

    private $wrongMultipleA1 = '{
        "answerId": {
            "1": true,
            "2": true,
            "3": true,
            "4": true
        }
    }';

    private $wrongMultipleA2 = '{
        "answerId": {
            "1": true,
            "2": true
        }
    }';

    private $missingA = '{
        "answerId": "3"
    }';

    private $linkingA = '{
        "answerId": {
            "1": "6",
            "2": "8",
            "3": "5",
            "4": "7"
        }
    }';

    private $emptySingleMissing = '{
        "answerId": ""
    }';

    private $emptyMultiple = '{
        "answerId": []
    }';

    private $emptyLinking = '{
        "answerId": {}
    }';

    public function RefreshId($array)
    {

        foreach ($array as $id => &$item) {
            $item['id'] = ++$id;
        }
        unset($item);

        return $array;
    }

    public function QuizQuestionGenerator($singleNum = null, $multipleNum = null, $missingNum = null, $linkingNum = null, $wrong = false)
    {

        $json2[] = 0;
        $json3[] = 0;
        if (! is_null($singleNum)) {
            for ($x = 0; $x < $singleNum; $x++) {
                $json[] = json_decode($this->singleQ, true);
                $json2[] = json_decode($this->singleA, true);
                $json3[] = json_decode($this->singleAt, true);
            }
        }

        if (! is_null($multipleNum)) {
            for ($x = 0; $x < $multipleNum; $x++) {
                $json[] = json_decode($this->multipleQ, true);
                if ($wrong == false) {
                    $json2[] = json_decode($this->multipleA, true);
                    $json3[] = json_decode($this->multipleAt, true);
                } else {
                    $json2[] = json_decode($this->wrongMultipleA1, true);
                    $json3[] = json_decode($this->multipleAt, true);
                }

            }
        }

        if (! is_null($missingNum)) {
            for ($x = 0; $x < $missingNum; $x++) {
                $json[] = json_decode($this->missingQ, true);
                $json2[] = json_decode($this->missingA, true);
                $json3[] = json_decode($this->missingAt, true);
            }
        }

        if (! is_null($linkingNum)) {
            for ($x = 0; $x < $linkingNum; $x++) {
                $json[] = json_decode($this->linkingQ, true);
                $json2[] = json_decode($this->linkingA, true);
                $json3[] = json_decode($this->linkingAt, true);
            }
        }

        $json = $this->RefreshId($json);
        $json = json_encode($json);

        $json2 = json_encode($json2, JSON_FORCE_OBJECT);
        $json2 = '{'.substr($json2, 7);

        $json3 = json_encode($json3, JSON_FORCE_OBJECT);
        $json3 = '{'.substr($json3, 7);

        $data = new \stdClass();
        $data->questions = $json;
        $data->answers = $json3;
        $data->answers2 = $json2;

        return $data;
    }

    public function QuizItemEntryGenerator()
    {

        $singleQ = json_decode($this->singleQ, true);
        $answer = json_decode($this->singleA, true);

        $data = new \stdClass();
        $data->question = $singleQ['question'];
        $data->options = json_encode($singleQ['options'], JSON_FORCE_OBJECT);
        $data->answer = json_encode($answer, JSON_FORCE_OBJECT);

        return $data;
    }

    public function QuizAnswersGenerator($singleNum = 0, $multipleNum = 0, $missingNum = 0, $linkingNum = 0, $wrongMultiple = false)
    {

        $json2[] = 0;

        if ($singleNum > 0) {
            for ($x = 0; $x < $singleNum; $x++) {
                $json2[] = json_decode($this->singleA, true);
            }
        }

        if ($singleNum < 0) {
            for ($x = 0; $x > $singleNum; $x--) {
                $json2[] = json_decode($this->emptySingleMissing, true);
            }
        }

        if ($multipleNum > 0) {
            for ($x = 0; $x < $multipleNum; $x++) {
                $wrongMultiple ? $json2[] = json_decode($this->wrongMultipleA2, true) : $json2[] = json_decode($this->multipleA, true);
            }
        }

        if ($multipleNum < 0) {
            for ($x = 0; $x > $multipleNum; $x--) {
                $json2[] = json_decode($this->emptyMultiple, true);
            }
        }

        if ($missingNum > 0) {
            for ($x = 0; $x < $missingNum; $x++) {
                $json2[] = json_decode($this->missingA, true);
            }
        }

        if ($missingNum < 0) {
            for ($x = 0; $x > $missingNum; $x--) {
                $json2[] = json_decode($this->emptySingleMissing, true);
            }
        }

        if ($linkingNum > 0) {
            for ($x = 0; $x < $linkingNum; $x++) {
                $json2[] = json_decode($this->linkingA, true);
            }
        }

        if ($linkingNum < 0) {
            for ($x = 0; $x > $linkingNum; $x--) {
                $json2[] = json_decode($this->emptyLinking, true);
            }
        }

        $json2 = json_encode($json2, JSON_FORCE_OBJECT);
        $json2 = '{'.substr($json2, 7);

        $data = new \stdClass();
        $data->answers = $json2;

        return $data;
    }
}
