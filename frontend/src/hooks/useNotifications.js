import { useCallback, useEffect, useState } from 'react';
import echo from '../services/echo';
import { useAuth } from './useAuth';

export default function useNotifications() {
  const { user } = useAuth();
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);

  const addNotification = useCallback((notification) => {
    setNotifications((previous) => [
      { ...notification, id: Date.now() + Math.random(), read: false },
      ...previous,
    ].slice(0, 20));
    setUnreadCount((previous) => previous + 1);
  }, []);

  const markAllRead = useCallback(() => {
    setNotifications((previous) => previous.map((notification) => ({
      ...notification,
      read: true,
    })));
    setUnreadCount(0);
  }, []);

  useEffect(() => {
    if (!user) {
      return undefined;
    }

    let channelName = null;
    let channel = null;

    if (user.role === 'student') {
      channelName = `student.${user.id}`;
      channel = echo.private(channelName);
      channel.listen('.absence.created', addNotification);
      channel.listen('.grade.created', addNotification);
    }

    if (user.role === 'parent') {
      channelName = `parent.${user.id}`;
      channel = echo.private(channelName);
      channel.listen('.absence.created', addNotification);
    }

    return () => {
      if (channelName) {
        echo.leave(channelName);
      }
    };
  }, [user, addNotification]);

  return { notifications, unreadCount, markAllRead };
}
