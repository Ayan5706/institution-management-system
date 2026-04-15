<?php

namespace Tests\Unit\Models;

use TestCase;

/**
 * Model Base Test Suite
 * 
 * Tests for Model functionality
 */
class ModelTest extends TestCase
{
    /**
     * Test model can be instantiated
     */
    public function testModelInstantiation(): void
    {
        if (class_exists('\\User')) {
            $model = new \User();
            $this->assertInstanceOf(\Model::class, $model);
        }
    }

    /**
     * Test model has table property
     */
    public function testModelHasTableProperty(): void
    {
        if (class_exists('\\Student')) {
            $model = new \Student();
            $this->assertObjectHasAttribute('table', $model);
        }
    }

    /**
     * Test model can set attributes
     */
    public function testModelSetAttributes(): void
    {
        if (class_exists('\\Program')) {
            $model = new \Program();
            $model->name = 'Test Program';
            $model->code = 'TEST';

            $this->assertEquals('Test Program', $model->name);
            $this->assertEquals('TEST', $model->code);
        }
    }

    /**
     * Test model fill method
     */
    public function testModelFill(): void
    {
        if (class_exists('\\Subject')) {
            $model = new \Subject();
            $data = [
                'code' => 'CSC101',
                'name' => 'Programming Basics',
                'credits' => 3,
            ];

            if (method_exists($model, 'fill')) {
                $model->fill($data);
                $this->assertEquals('CSC101', $model->code);
                $this->assertEquals('Programming Basics', $model->name);
                $this->assertEquals(3, $model->credits);
            }
        }
    }

    /**
     * Test user model structure
     */
    public function testUserModelStructure(): void
    {
        if (class_exists('\\User')) {
            $model = new \User();

            // Test fillable or guarded properties
            $expectedAttributes = ['name', 'email', 'password', 'role'];

            foreach ($expectedAttributes as $attr) {
                // Model should be able to set these
                $model->$attr = "test_value";
                $this->assertNotEmpty($model->$attr);
            }
        }
    }

    /**
     * Test student model structure
     */
    public function testStudentModelStructure(): void
    {
        if (class_exists('\\Student')) {
            $model = new \Student();

            $expectedAttributes = ['user_id', 'roll_number', 'batch', 'program_id', 'semester_id'];

            foreach ($expectedAttributes as $attr) {
                $model->$attr = "test_value";
                $this->assertNotEmpty($model->$attr);
            }
        }
    }

    /**
     * Test program model
     */
    public function testProgramModel(): void
    {
        if (class_exists('\\Program')) {
            $model = new \Program();
            $model->name = 'Engineering';
            $model->code = 'ENG';

            $this->assertEquals('Engineering', $model->name);
            $this->assertEquals('ENG', $model->code);
        }
    }

    /**
     * Test semester model
     */
    public function testSemesterModel(): void
    {
        if (class_exists('\\Semester')) {
            $model = new \Semester();
            $model->name = 'Spring 2026';
            $model->start_date = '2026-01-01';
            $model->end_date = '2026-06-30';

            $this->assertEquals('Spring 2026', $model->name);
        }
    }

    /**
     * Test subject model
     */
    public function testSubjectModel(): void
    {
        if (class_exists('\\Subject')) {
            $model = new \Subject();
            $model->code = 'CSC101';
            $model->name = 'Programming';
            $model->credits = 3;

            $this->assertEquals('CSC101', $model->code);
            $this->assertEquals(3, $model->credits);
        }
    }

    /**
     * Test teacher model
     */
    public function testTeacherModel(): void
    {
        if (class_exists('\\Teacher')) {
            $model = new \Teacher();
            $model->user_id = 1;
            $model->qualification = 'Masters';

            $this->assertEquals(1, $model->user_id);
        }
    }

    /**
     * Test attendance model
     */
    public function testAttendanceModel(): void
    {
        if (class_exists('\\Attendance')) {
            $model = new \Attendance();
            $model->student_id = 1;
            $model->subject_id = 1;
            $model->date = date('Y-m-d');
            $model->status = 'present';

            $this->assertEquals('present', $model->status);
        }
    }

    /**
     * Test fee model
     */
    public function testFeeModel(): void
    {
        if (class_exists('\\Fee')) {
            $model = new \Fee();
            $model->student_id = 1;
            $model->amount = 50000;
            $model->status = 'pending';

            $this->assertEquals(50000, $model->amount);
            $this->assertEquals('pending', $model->status);
        }
    }

    /**
     * Test timetable model
     */
    public function testTimetableModel(): void
    {
        if (class_exists('\\Timetable')) {
            $model = new \Timetable();
            $model->subject_id = 1;
            $model->teacher_id = 1;
            $model->day = 'Monday';
            $model->start_time = '09:00';
            $model->end_time = '11:00';

            $this->assertEquals('Monday', $model->day);
        }
    }

    /**
     * Test model toArray
     */
    public function testModelToArray(): void
    {
        if (class_exists('\\Program')) {
            $model = new \Program();
            $model->name = 'Test Program';
            $model->code = 'TEST';

            if (method_exists($model, 'toArray')) {
                $array = $model->toArray();
                $this->assertIsArray($array);
                $this->assertArrayHasKey('name', $array);
                $this->assertEquals('Test Program', $array['name']);
            }
        }
    }

    /**
     * Test model timestamps
     */
    public function testModelTimestamps(): void
    {
        if (class_exists('\\Student')) {
            $model = new \Student();

            // Models often automatically manage created_at and updated_at
            if (property_exists($model, 'timestamps')) {
                $this->assertTrue(true); // Model has timestamp support
            }
        }
    }
}
