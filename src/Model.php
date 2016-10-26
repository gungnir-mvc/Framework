<?php
namespace Gungnir\Framework;

/**
 * @package gungnir-mvc\framework
 * @author Conny Karlsson <connykarlsson9@gmail.com>
 */
class Model
{
    /** array $required Array with fields required to save a model */
    protected $required = [];

    /**
     * Add a requirement to this models requirement stack
     *
     * @param string $requirement The requirement
     *
     * @return self
     */
    public function addRequirement(String $requirement)
    {
        $this->required[] = $requirement;
    }

    /**
     * Get all requirements for this model
     *
     * @return array
     */
    public function getRequirements()
    {
        return $this->required;
    }

    /**
     * Validates a model against this models requirements
     *
     * @param Model $model The model to validate
     *
     * @return bool Valid or not
     */
    public function validateModel(Model $model)
    {
        $valid = true;
        foreach ($this->getRequirements() as $requirement) {
            if (empty($model->{$requirement})) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Validates this model against it's requirements
     *
     * @return bool Valid or not
     */
    public function validate()
    {
        return $this->validateModel($this);
    }
}
