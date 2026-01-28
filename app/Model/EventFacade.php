<?php

namespace App\Model;

use Nette\Database\Explorer;

final class EventFacade
{
    private Explorer $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function findApproved()
    {
        return $this->db->table('events')
            ->where('status', 'approved')
            ->order('start_time ASC');
    }

    public function findAll()
    {
        return $this->db->table('events')
            ->order('start_time ASC');
    }

    public function getById(int $id)
    {
        return $this->db->table('events')->get($id);
    }

    public function addParticipant(int $eventId, int $userId): void
    {
        if (!$this->isUserJoined($eventId, $userId)) {
            $this->db->table('event_participants')->insert([
                'event_id' => $eventId,
                'user_id' => $userId,
            ]);
        }
    }

    public function getAllEvents(): array
{
    return $this->db->table('events')->order('start_time ASC')->fetchAll();
}

public function getUpcomingEvents(): array
{
    return $this->db->table('events')
        ->where('start_time >= ?', new \DateTime())
        ->where('status = ?', 'approved')
        ->order('start_time ASC')
        ->fetchAll();
}

public function getPastEvents(): array
{
    return $this->db->table('events')
        ->where('start_time < ?', new \DateTime())
        ->where('status = ?', 'approved')
        ->order('start_time DESC')
        ->fetchAll();
}

public function getPendingEvents(): array
{
    return $this->db->table('events')
        ->where('status = ?', 'pending')
        ->order('start_time ASC')
        ->fetchAll();
}

public function countAllEvents(): int
{
    return $this->db->table('events')->count('*');
}

// Добавьте эти методы в EventFacade
public function getUpcomingEventsPaginated(int $limit = 9, int $offset = 0, string $keyword = ''): array
{
    $query = $this->db->table('events')
        ->where('start_time >= ?', new \DateTime())
        ->where('status = ?', 'approved')
        ->order('start_time ASC');

    if ($keyword) {
        $query->where(
            'title LIKE ? OR location LIKE ? OR description LIKE ? OR DATE_FORMAT(start_time, "%d.%m.%Y") LIKE ? OR DATE_FORMAT(start_time, "%Y-%m-%d") LIKE ? OR DATE_FORMAT(start_time, "%d.%m.") LIKE ?',
            "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%"
        );
    }

    return $query->limit($limit, $offset)->fetchAll();
}

public function countUpcomingEvents(string $keyword = ''): int
{
    $query = $this->db->table('events')
        ->where('start_time >= ?', new \DateTime())
        ->where('status = ?', 'approved');

    if ($keyword) {
        $query->where(
            'title LIKE ? OR location LIKE ? OR description LIKE ? OR DATE_FORMAT(start_time, "%d.%m.%Y") LIKE ? OR DATE_FORMAT(start_time, "%Y-%m-%d") LIKE ? OR DATE_FORMAT(start_time, "%d.%m.") LIKE ?',
            "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%"
        );
    }

    return $query->count('*');
}

public function countPastEvents(): int
{
    return $this->db->table('events')->where('start_time < ?', new \DateTime())->where('status = ?', 'approved')->count('*');
}

public function countPendingEvents(): int
{
    return $this->db->table('events')->where('status = ?', 'pending')->count('*');
}


    public function getEventsByOrganizer(int $organizerId)
    {
        return $this->db->table('events')->where('organizer_id', $organizerId)->fetchAll();
    }

public function getEventById(int $id)
{
    return $this->db->table('events')->get($id);
}

public function isUserJoined(int $eventId, int $userId): bool
{
    return (bool) $this->db->table('event_participants')
        ->where('event_id', $eventId)
        ->where('user_id', $userId)
        ->count('*');
}

public function joinEvent(int $eventId, int $userId): void
{
    $this->db->table('event_participants')->insert([
        'event_id' => $eventId,
        'user_id' => $userId,
        'joined_at' => new \DateTime(),
    ]);
}

public function leaveEvent(int $eventId, int $userId): void
{
    $this->db->table('event_participants')
        ->where('event_id', $eventId)
        ->where('user_id', $userId)
        ->delete();
}

public function createEvent(array $data): void
{
    $this->db->table('events')->insert([
        'title' => $data['title'],
        'location' => $data['location'],
        'start_time' => $data['start_time'],
        'end_time' => $data['end_time'],
        'description' => $data['description'],
        'status' => 'approved',
        'organizer_id' => 1, // временно, можно подставить $user->getId()
    ]);
}

public function updateEvent(int $id, array $data): void
{
    $this->db->table('events')->where('id', $id)->update($data);
}

public function deleteEvent(int $id): void
{
    $this->db->table('events')->where('id', $id)->delete();
}

public function getChatMessages(int $eventId): array
{
    return $this->db->query(
        'SELECT c.*, u.username, r.name AS role FROM comments c 
         JOIN users u ON c.user_id = u.id 
         JOIN roles r ON u.role_id = r.id
         WHERE c.event_id = ? ORDER BY c.created_at ASC', $eventId
    )->fetchAll();
}

public function addChatMessage(int $eventId, int $userId, string $content): void
{
    $this->db->table('comments')->insert([
        'event_id' => $eventId,
        'user_id' => $userId,
        'content' => $content,
        'created_at' => new \DateTime(),
    ]);
}

public function deleteChatMessage(int $messageId): void
{
    $this->db->table('comments')->where('id', $messageId)->delete();
}

public function deleteAllChatMessagesForEvent(int $eventId): void
{
    $this->db->table('comments')->where('event_id', $eventId)->delete();
}
}