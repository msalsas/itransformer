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

use Herzult\Bundle\ForumBundle\Entity\Post as BasePost;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Herzult\Bundle\ForumBundle\Entity\PostRepository")
 */
class Post extends BasePost
{
    /**
     * @ORM\ManyToOne(targetEntity="Topic")
     */
    protected $topic;
    
     /**
     * @Assert\Regex(
	 * pattern="/^[[a-zçñA-ZÇÑ0-9áéíóúÁÉÍÓÚ\?¿!¡,;:\.\-_\(\)\n\s\']{4,1000}$/",
	 * message="The message can not contain weird symbols (quotes, percent sign, ...). Between 4 and 1000 characters"
	 * )
     *
     */
    protected $message;

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
