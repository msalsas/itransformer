<?php
/*
 * Itransformer.es is an online application to transform images
Copyright (C) 2013  Manolo Salsas

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Contact: manolez@gmail.com - http://msalsas.com
* */


namespace MSD\ForoBundle\Entity;

use Herzult\Bundle\ForumBundle\Entity\Topic as BaseTopic;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Topic
 *
 * @ORM\Entity(repositoryClass="Herzult\Bundle\ForumBundle\Entity\TopicRepository")
 * 
 */
class Topic extends BaseTopic
{
    /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    protected $category;
    
    /**
     * @Assert\NotBlank()
     * @Assert\MinLength(limit=4, message="Just a little too short| ")
     * @Assert\Regex(
	 * pattern="/^[a-zA-ZáéíóúÁÉÍÓÚ0-9\-_¿?!¡\s\']{4,50}$/",
	 * message="The topic can contain numbers, dashes and blank spaces, question and exclamation marks. Between 4 and 30 characters"
	 * )
    */
    protected $subject;
   /**
     * @Assert\NotBlank
     * @Assert\Valid
     */
    protected $firstPost;
    
    /**
     * {@inheritDoc}
     */
    public function getAuthorName()
    {
        return $this->author;
    }
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $author;

    public function setAuthor(User $user)
    {
        $this->author = $user;
    }

    public function getAuthor()
    {
        return $this->author;
    }
}
