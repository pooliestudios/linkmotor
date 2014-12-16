<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Import
 *
 * @ORM\Table(name="imports")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\ImportRepository")
 */
class Import
{
    const TYPE_LINKBIRD = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * var integer 1 = Linkbird
     *
     * @ORM\Column(name="type", type="integer");
     */
    protected $type;

    /**
     * @var string filename
     *
     * @ORM\Column(name="filename", type="string", length=128)
     */
    protected $filename;

    /**
     * @var string hash - is used to store the input- and output-files
     *
     * @ORM\Column(name="hash", type="string", length=32)
     */
    protected $hash;

    /**
     * @var integer
     *
     * @ORM\Column(name="step", type="integer");
     */
    protected $step;

    /**
     * @var array (stored as JSON) - holds additional information, like the result overview of the import
     *
     * @ORM\Column(name="data", type="text");
     */
    protected $data;

    /**
     * @var integer
     *
     * @ORM\Column(name="progress", type="integer");
     */
    protected $progress;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="imports")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="imports")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $createdBy;

    public function __construct()
    {
        $this->setStep(0);
        $this->setProgress(0);
        $this->setData(array());
        $this->setCreatedAt(new \DateTime());
    }

    public function getNumRowsImported()
    {
        $data = $this->getData();

        return isset($data['numRowsImported']) ? $data['numRowsImported'] : 0;
    }

    public function getDisplayStatus()
    {
        if ($this->step == 1) {
            return 'Analyzing import';
        } elseif ($this->step == 3) {
            return 'Waiting for data import';
        } elseif ($this->step == 4) {
            return 'Importing';
        } elseif ($this->step == 99) {
            $data = $this->getData();
            return $data['msg'];
        }

        return 'Waiting for analyze';
    }

    public function statusIsWaiting()
    {
        if ($this->step < 99 && $this->step != 2) {
            return true;
        }

        return false;
    }

    public function getImportFilename()
    {
        return $this->getHash() . '-' . $this->getProject()->getId();
    }

    public function getTranscriptFilename()
    {
        return $this->getHash() . '-' . $this->getProject()->getId() . '.transcript';
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/imports';
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Import
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return Import
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    
        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set step
     *
     * @param integer $step
     * @return Import
     */
    public function setStep($step)
    {
        $this->step = $step;
    
        return $this;
    }

    /**
     * Get step
     *
     * @return integer 
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return Import
     */
    public function setData($data)
    {
        if (!is_array($data)) {
            $data = array();
        }
        $this->data = @json_encode($data);

        return $this;
    }

    /**
     * Get data
     *
     * @return string 
     */
    public function getData()
    {
        $data = @json_decode($this->data, true);
        if (!is_array($data)) {
            $data = array();
        }
        return $data;
    }

    /**
     * Set progress
     *
     * @param integer $progress
     * @return Import
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;
    
        return $this;
    }

    /**
     * Get progress
     *
     * @return integer 
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set createdBy
     *
     * @param \Pool\LinkmotorBundle\Entity\User $createdBy
     * @return Import
     */
    public function setCreatedBy(User $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Pool\LinkmotorBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Import
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        $this->setHash(md5($filename . time()));

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Import
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return Import
     */
    public function setProject(\Pool\LinkmotorBundle\Entity\Project $project = null)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Get project
     *
     * @return \Pool\LinkmotorBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
